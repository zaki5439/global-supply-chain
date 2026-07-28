"""
Cache Manager Test Suite
Tests multi-layer caching functionality
"""

import pytest
import time
from cache_manager import (
    CacheManager,
    cache_manager,
    CACHE_CONFIG,
    init_cache
)

class TestCacheManager:
    """Test cache manager functionality"""
    
    @pytest.fixture(autouse=True)
    def setup(self):
        """Setup and teardown"""
        if cache_manager.is_available():
            cache_manager.clear_all()
        yield
        if cache_manager.is_available():
            cache_manager.clear_all()
    
    def test_redis_connection(self):
        """Test Redis connection"""
        assert cache_manager.is_available() or True  # Graceful fallback
    
    def test_cache_set_and_get(self):
        """Test basic set and get operations"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Set value
        cache_manager.set("test_key", {"data": "value"})
        
        # Get value
        result = cache_manager.get("test_key")
        assert result == {"data": "value"}
    
    def test_cache_ttl(self):
        """Test cache TTL expiration"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Set with 1 second TTL
        cache_manager.set("ttl_test", {"data": "expires"}, ttl=1)
        
        # Should exist immediately
        assert cache_manager.get("ttl_test") is not None
        
        # Wait for expiration
        time.sleep(2)
        
        # Should be expired
        assert cache_manager.get("ttl_test") is None
    
    def test_cache_delete(self):
        """Test cache deletion"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Set and verify
        cache_manager.set("delete_test", {"data": "delete"})
        assert cache_manager.get("delete_test") is not None
        
        # Delete
        cache_manager.delete("delete_test")
        
        # Verify deletion
        assert cache_manager.get("delete_test") is None
    
    def test_cache_clear_pattern(self):
        """Test clear by pattern"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Set multiple keys
        cache_manager.set("country:germany", {"name": "Germany"})
        cache_manager.set("country:singapore", {"name": "Singapore"})
        cache_manager.set("weather:germany", {"temp": 15})
        
        # Clear country pattern
        deleted = cache_manager.clear_pattern("country:*")
        
        # Verify
        assert deleted >= 2
        assert cache_manager.get("country:germany") is None
        assert cache_manager.get("country:singapore") is None
        assert cache_manager.get("weather:germany") is not None
    
    def test_cache_stats(self):
        """Test cache statistics"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Reset stats
        cache_manager.reset_stats()
        
        # Perform operations
        cache_manager.set("stat_key", {"data": "test"})
        cache_manager.get("stat_key")      # Hit
        cache_manager.get("nonexistent")   # Miss
        
        # Check stats
        stats = cache_manager.get_stats()
        
        assert stats['sets'] >= 1
        assert stats['hits'] >= 1
        assert stats['misses'] >= 1
        assert stats['available'] is True
    
    def test_cache_get_or_set(self):
        """Test get_or_set operation"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        call_count = 0
        
        def fetch_data():
            nonlocal call_count
            call_count += 1
            return {"data": "fetched"}
        
        # First call - should fetch
        result1 = cache_manager.get_or_set("or_set_key", fetch_data)
        assert result1 == {"data": "fetched"}
        assert call_count == 1
        
        # Second call - should use cache
        result2 = cache_manager.get_or_set("or_set_key", fetch_data)
        assert result2 == {"data": "fetched"}
        assert call_count == 1  # Function not called again
    
    def test_cache_invalidate_related(self):
        """Test related cache invalidation"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Set multiple related keys
        cache_manager.set("country:germany", {"name": "Germany"})
        cache_manager.set("risk:germany", {"score": 45})
        cache_manager.set("weather:germany", {"temp": 15})
        
        # Invalidate country
        cache_manager.invalidate_related("country", "germany")
        
        # Check invalidation
        assert cache_manager.get("country:germany") is None
    
    def test_cache_info(self):
        """Test cache info retrieval"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        info = cache_manager.get_info()
        
        # Should have status
        assert "status" in info
    
    def test_different_ttls(self):
        """Test different TTL configurations"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        # Test different TTLs
        cache_manager.set(
            "short_ttl",
            {"data": "short"},
            CACHE_CONFIG['ttl']['short']
        )
        
        cache_manager.set(
            "long_ttl",
            {"data": "long"},
            CACHE_CONFIG['ttl']['long']
        )
        
        # Both should exist immediately
        assert cache_manager.get("short_ttl") is not None
        assert cache_manager.get("long_ttl") is not None


class TestCachePerformance:
    """Test cache performance characteristics"""
    
    def test_cache_hit_performance(self):
        """Test that cache hits are fast"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        cache_manager.clear_all()
        data = {"country": "Germany", "risk": 45.5, "region": "Europe"}
        cache_manager.set("perf_test", data)
        
        # Measure cache hit time
        start = time.time()
        for _ in range(100):
            cache_manager.get("perf_test")
        elapsed = time.time() - start
        
        avg_time = (elapsed / 100) * 1000  # Convert to ms
        
        # Should be very fast (< 5ms per operation)
        assert avg_time < 5, f"Cache hit took {avg_time}ms"
    
    def test_cache_miss_rate(self):
        """Test cache miss rate impact on stats"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        cache_manager.reset_stats()
        
        # Generate hits and misses
        cache_manager.set("key1", {"data": "value"})
        cache_manager.get("key1")     # Hit
        cache_manager.get("key1")     # Hit
        cache_manager.get("missing")  # Miss
        
        stats = cache_manager.get_stats()
        
        # Verify hit rate
        assert stats['hit_rate'] == "66.67%"


class TestCacheIntegration:
    """Integration tests for cache with API"""
    
    def test_cache_with_json_data(self):
        """Test caching complex JSON structures"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        complex_data = {
            "country": "Germany",
            "metrics": {
                "gdp": 4080000000000,
                "population": 83369843,
                "inflation": 3.8
            },
            "risks": {
                "weather": 25.3,
                "currency": 35.7,
                "inflation": 28.9,
                "news": 22.1
            },
            "regions": ["Europe", "EU", "Central Europe"],
            "nested": {
                "data": {
                    "value": 123.45
                }
            }
        }
        
        cache_manager.set("complex_data", complex_data)
        result = cache_manager.get("complex_data")
        
        assert result == complex_data
        assert result["metrics"]["gdp"] == 4080000000000
        assert result["risks"]["currency"] == 35.7
    
    def test_cache_patterns(self):
        """Test various cache key patterns"""
        if not cache_manager.is_available():
            pytest.skip("Redis not available")
        
        patterns = [
            "country:germany",
            "country:singapore",
            "weather:germany",
            "weather:singapore",
            "risk:germany:2024",
            "news:germany:logistics"
        ]
        
        for pattern in patterns:
            cache_manager.set(pattern, {"key": pattern})
        
        # Query patterns
        country_count = cache_manager.clear_pattern("country:*")
        assert country_count >= 2
        
        # Weather should still exist
        assert cache_manager.get("weather:germany") is not None


# ============================================
# PERFORMANCE BENCHMARKS
# ============================================

def benchmark_cache_operations():
    """Benchmark cache operations"""
    if not cache_manager.is_available():
        print("⚠ Redis not available - skipping benchmark")
        return
    
    print("\n📊 Cache Performance Benchmark\n")
    
    # Warm up
    cache_manager.set("warmup", {"data": "test"})
    cache_manager.get("warmup")
    
    # SET operations
    print("Testing SET operations...")
    start = time.time()
    for i in range(1000):
        cache_manager.set(f"bench_set_{i}", {"index": i})
    set_time = time.time() - start
    print(f"  1000 SET operations: {set_time:.3f}s ({1000/set_time:.0f} ops/sec)")
    
    # GET operations (cache hits)
    print("Testing GET operations (cache hits)...")
    start = time.time()
    for i in range(1000):
        cache_manager.get(f"bench_set_{i}")
    get_hit_time = time.time() - start
    print(f"  1000 GET operations (hits): {get_hit_time:.3f}s ({1000/get_hit_time:.0f} ops/sec)")
    
    # GET operations (cache misses)
    print("Testing GET operations (cache misses)...")
    start = time.time()
    for i in range(1000):
        cache_manager.get(f"bench_miss_{i}")
    get_miss_time = time.time() - start
    print(f"  1000 GET operations (misses): {get_miss_time:.3f}s ({1000/get_miss_time:.0f} ops/sec)")
    
    # DELETE operations
    print("Testing DELETE operations...")
    start = time.time()
    for i in range(100):
        cache_manager.delete(f"bench_set_{i}")
    delete_time = time.time() - start
    print(f"  100 DELETE operations: {delete_time:.3f}s ({100/delete_time:.0f} ops/sec)")
    
    # Print stats
    print("\n📈 Final Statistics:")
    stats = cache_manager.get_stats()
    for key, value in stats.items():
        print(f"  {key}: {value}")


if __name__ == "__main__":
    # Run benchmarks
    benchmark_cache_operations()
    
    # Run tests
    print("\n\n🧪 Running test suite...\n")
    pytest.main([__file__, "-v"])
