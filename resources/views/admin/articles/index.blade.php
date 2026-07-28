<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', sans-serif; }
        .header-section {
            background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%);
            border-bottom: 1px solid rgba(102,126,234,0.2);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
<div class="d-flex">
    @include('admin.sidebar')
    
    <main class="flex-grow-1 p-4">
        <div class="header-section d-flex justify-content-between align-items-center">
            <div>
                <h2 style="color: #1a1d2e; font-weight: 700;"><i class="bi bi-newspaper me-2"></i>Analysis Articles</h2>
                <p class="text-muted mb-0">Manage manually curated news and supply chain analysis</p>
            </div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addArticleModal">
                <i class="bi bi-plus-circle me-1"></i> Add New Article
            </button>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Source</th>
                            <th>Sentiment</th>
                            <th>Published Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                        <tr>
                            <td class="fw-bold">
                                <a href="{{ $article->url }}" target="_blank" class="text-decoration-none">{{ Str::limit($article->title, 50) }}</a>
                            </td>
                            <td>{{ $article->source_name }}</td>
                            <td>
                                @if($article->sentiment_label === 'positive')
                                    <span class="badge bg-success">Positive</span>
                                @elseif($article->sentiment_label === 'negative')
                                    <span class="badge bg-danger">Negative</span>
                                @else
                                    <span class="badge bg-secondary">Neutral</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($article->published_at)->format('Y-m-d') }}</td>
                            <td>
                                <form action="{{ route('articles.destroy', $article->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this article?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No articles found in database.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $articles->links() }}
            </div>
        </div>
    </main>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addArticleModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('articles.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add New Article</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>URL / Link</label>
                    <input type="url" name="url" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Source Name</label>
                    <input type="text" name="source_name" class="form-control" placeholder="e.g. Reuters, Bloomberg" required>
                </div>
                <div class="mb-3">
                    <label>Published Date</label>
                    <input type="date" name="published_at" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Manual Sentiment</label>
                    <select name="sentiment_label" class="form-select">
                        <option value="neutral">Neutral</option>
                        <option value="positive">Positive</option>
                        <option value="negative">Negative</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Article</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
