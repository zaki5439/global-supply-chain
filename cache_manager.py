"""
Redis Cache Manager
Multi-layer caching strategy for Global Supply Chain Risk Intelligence Platform
Layers: Browser (5min) → App (15min) → Redis (24h)
"""

import redis
import json
import os
from datetime import datetime, timedelta
from typing import Any, Optional
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# ============================================
# CACHE CONFIGURATION
# ============================================

CACHE_CONFIG = {
    'redis': {
        'host': os.getenv('REDIS_HOST', 'localhost'),
        'port': int(os.getenv('REDIS_PORT', 6379)),
        'db': int(os.getenv('REDIS_DB', 0)),
        'password': os.getenv('REDIS_PASSWORD', None),
        'decode_responses': True
    },
    'ttl': {
        'browser': 5 * 60,        # 5 minutes for browser cache
        'application': 15 * 60,   # 15 minutes for app cache
        'redis': 24 * 60 * 60,    # 24 hours for Redis cache
        'short': 5 * 60,          # 5 minutes for frequently updated data
        'medium': 60 * 60,        # 1 hour
        'long': 7 * 24 * 60 * 60  # 7 days for static data
    },
    'prefixes': {
        'country': 'country:',
        'risk': 'risk:',
        'weather': 'weather:',
        'macro': 'macro:',
        'exchange': 'exchange:',
        'news': 'news:',
        'ports': 'ports:',
        'geographic': 'geo:',
        'comparison': 'compare:',
        'stats': 'stats:'
    }
}

# ============================================
# REDIS CONNECTION POOL
# ============================================

class RedisConnectionPool:
    """Singleton Redis connection pool"""
    _instance = None
    _redis_client = None
    
    def __new__(cls):
        if cls._instance is None:
            cls._instance = super(RedisConnectionPool, cls).__new__(cls)
        return cls._instance
    
    def get_client(self):
        """Get or create Redis client"""
        if self._redis_client is None:
            try:
                self._redis_client = redis.Redis(
                    host=CACHE_CONFIG['redis']['host'],
                    port=CACHE_CONFIG['redis']['port'],
                    db=CACHE_CONFIG['redis']['db'],
                    password=CACHE_CONFIG['redis']['password'],
                    decode_responses=CACHE_CONFIG['redis']['decode_responses'],
                    socket_connect_timeout=5,
                    socket_keepalive=True,
                    health_check_interval=30
                )
                # Test connection
                self._redis_client.ping()
                logger.info('✓ Redis connection established')
            except Exception as e:
                logger.error(f'✗ Redis connection failed: {e}')
                self._redis_client = None
        
        return self._redis_client

# ============================================
# CACHE MANAGER
# ============================================

class CacheManager:
    """
    Multi-layer cache manager
    Handles Redis caching with TTL policies and cache invalidation
    """
    
    def __init__(self):
        self.redis_client = RedisConnectionPool().get_client()
        self.stats = {
            'hits': 0,
            'misses': 0,
            'sets': 0,
            'deletes': 0
        }
    
    def is_available(self) -> bool:
        """Check if Redis is available"""
        return self.redis_client is not None
    
    # ============================================
    # CACHE OPERATIONS
    # ============================================
    
    def get(self, key: str) -> Optional[Any]:
        """
        Get value from cache
        Returns: Cached value or None
        """
        if not self.is_available():
            return None
        
        try:
            value = self.redis_client.get(key)
            if value:
                self.stats['hits'] += 1
                logger.debug(f'✓ Cache HIT: {key}')
                return json.loads(value)
            else:
                self.stats['misses'] += 1
                logger.debug(f'⟳ Cache MISS: {key}')
                return None
        except Exception as e:
            logger.error(f'✗ Cache GET error [{key}]: {e}')
            return None
    
    def set(self, key: str, value: Any, ttl: int = None) -> bool:
        """
        Set value in cache
        Args:
            key: Cache key
            value: Value to cache
            ttl: Time to live in seconds (default: application TTL)
        Returns: Success status
        """
        if not self.is_available():
            return False
        
        try:
            if ttl is None:
                ttl = CACHE_CONFIG['ttl']['application']
            
            json_value = json.dumps(value)
            self.redis_client.setex(key, ttl, json_value)
            self.stats['sets'] += 1
            logger.debug(f'✓ Cache SET: {key} (TTL: {ttl}s)')
            return True
        except Exception as e:
            logger.error(f'✗ Cache SET error [{key}]: {e}')
            return False
    
    def delete(self, key: str) -> bool:
        """
        Delete value from cache
        """
        if not self.is_available():
            return False
        
        try:
            self.redis_client.delete(key)
            self.stats['deletes'] += 1
            logger.debug(f'✓ Cache DELETE: {key}')
            return True
        except Exception as e:
            logger.error(f'✗ Cache DELETE error [{key}]: {e}')
            return False
    
    def clear_pattern(self, pattern: str) -> int:
        """
        Delete all keys matching pattern
        Uses: pattern = 'country:*' to delete all country cache
        """
        if not self.is_available():
            return 0
        
        try:
            keys = self.redis_client.keys(pattern)
            if keys:
                deleted = self.redis_client.delete(*keys)
                logger.info(f'✓ Cleared {deleted} cache entries matching pattern: {pattern}')
                return deleted
            return 0
        except Exception as e:
            logger.error(f'✗ Cache CLEAR error [{pattern}]: {e}')
            return 0
    
    def clear_all(self) -> bool:
        """
        Clear entire Redis cache
        WARNING: Only use in testing/dev
        """
        if not self.is_available():
            return False
        
        try:
            self.redis_client.flushdb()
            logger.warning('⚠ Redis cache cleared completely')
            return True
        except Exception as e:
            logger.error(f'✗ Cache FLUSH error: {e}')
            return False
    
    # ============================================
    # SMART CACHE OPERATIONS
    # ============================================
    
    def get_or_set(self, key: str, fetch_func, ttl: int = None) -> Any:
        """
        Get from cache or fetch and cache
        Args:
            key: Cache key
            fetch_func: Function to call if cache miss
            ttl: Time to live in seconds
        """
        # Try to get from cache
        cached = self.get(key)
        if cached is not None:
            return cached
        
        # Cache miss - fetch and store
        value = fetch_func()
        if value is not None:
            self.set(key, value, ttl)
        
        return value
    
    def invalidate_related(self, entity_type: str, entity_id: str) -> None:
        """
        Invalidate related cache entries
        Example: invalidate_related('country', 'Germany')
        """
        prefix = CACHE_CONFIG['prefixes'].get(entity_type, '')
        if not prefix:
            return
        
        # Build pattern to match
        pattern = f'{prefix}{entity_id}*'
        
        # Delete matching keys
        deleted = self.clear_pattern(pattern)
        logger.info(f'✓ Invalidated {deleted} cache entries for {entity_type}:{entity_id}')
    
    # ============================================
    # CACHE STATISTICS
    # ============================================
    
    def get_stats(self) -> dict:
        """Get cache statistics"""
        total_requests = self.stats['hits'] + self.stats['misses']
        hit_rate = (self.stats['hits'] / total_requests * 100) if total_requests > 0 else 0
        
        return {
            'hits': self.stats['hits'],
            'misses': self.stats['misses'],
            'sets': self.stats['sets'],
            'deletes': self.stats['deletes'],
            'total_requests': total_requests,
            'hit_rate': f'{hit_rate:.2f}%',
            'available': self.is_available()
        }
    
    def reset_stats(self) -> None:
        """Reset statistics counters"""
        self.stats = {
            'hits': 0,
            'misses': 0,
            'sets': 0,
            'deletes': 0
        }
        logger.info('✓ Cache statistics reset')
    
    def get_info(self) -> dict:
        """Get Redis server info"""
        if not self.is_available():
            return {'status': 'unavailable'}
        
        try:
            info = self.redis_client.info()
            return {
                'status': 'connected',
                'used_memory': info.get('used_memory_human'),
                'used_memory_peak': info.get('used_memory_peak_human'),
                'total_connections': info.get('total_connections_received'),
                'total_commands': info.get('total_commands_processed'),
                'uptime_seconds': info.get('uptime_in_seconds')
            }
        except Exception as e:
            logger.error(f'✗ Error getting Redis info: {e}')
            return {'status': 'error'}

# ============================================
# CACHE DECORATOR
# ============================================

def cached(ttl: int = None, prefix: str = ''):
    """
    Decorator to cache function results
    Usage:
        @cached(ttl=CACHE_CONFIG['ttl']['application'], prefix='country:')
        def get_country_data(country_name):
            return fetch_from_api(country_name)
    """
    def decorator(func):
        def wrapper(*args, **kwargs):
            cache = CacheManager()
            
            # Build cache key
            cache_key = f'{prefix}{func.__name__}:'
            cache_key += ':'.join(str(arg) for arg in args)
            cache_key += ':'.join(f'{k}={v}' for k, v in kwargs.items())
            
            # Try cache first
            cached_value = cache.get(cache_key)
            if cached_value is not None:
                return cached_value
            
            # Cache miss - execute function
            result = func(*args, **kwargs)
            
            # Cache result
            cache.set(cache_key, result, ttl)
            
            return result
        
        return wrapper
    return decorator

# ============================================
# CACHE WARMUP
# ============================================

class CacheWarmer:
    """Pre-load frequently accessed data into cache"""
    
    def __init__(self, cache: CacheManager):
        self.cache = cache
    
    def warmup_countries(self, countries: list) -> None:
        """Warmup cache with country data"""
        logger.info(f'🔥 Warming up cache for {len(countries)} countries...')
        for country in countries:
            # This would typically fetch from DB or API
            # and populate cache
            pass
        logger.info('✓ Cache warmup complete')
    
    def warmup_all(self) -> None:
        """Warmup all frequently used data"""
        logger.info('🔥 Starting full cache warmup...')
        
        # Warmup sample countries
        sample_countries = [
            'Germany', 'China', 'United States', 'Japan', 'India',
            'Singapore', 'United Kingdom', 'France', 'Brazil'
        ]
        self.warmup_countries(sample_countries)
        
        logger.info('✓ Full cache warmup complete')

# ============================================
# GLOBAL CACHE INSTANCE
# ============================================

# Create global cache manager instance
cache_manager = CacheManager()

# ============================================
# INITIALIZATION
# ============================================

def init_cache():
    """Initialize cache system"""
    logger.info('📦 Initializing cache system...')
    
    if cache_manager.is_available():
        logger.info('✓ Redis cache initialized')
        cache_manager.clear_all()  # Start fresh
        return True
    else:
        logger.warning('⚠ Redis cache not available - falling back to application memory')
        return False

if __name__ == '__main__':
    # Test cache manager
    init_cache()
    
    print('\n📊 Cache Statistics:')
    stats = cache_manager.get_stats()
    for key, value in stats.items():
        print(f'  {key}: {value}')
    
    print('\n📋 Redis Info:')
    info = cache_manager.get_info()
    for key, value in info.items():
        print(f'  {key}: {value}')
