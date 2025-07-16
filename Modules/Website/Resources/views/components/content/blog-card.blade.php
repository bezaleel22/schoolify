<div class="blog-card">
    <div class="blog-image">
        @if($blog->featured_image)
            <img src="{{ asset('storage/' . $blog->featured_image) }}" alt="{{ $blog->title }}" class="img-fluid">
        @else
            <img src="{{ asset('images/blog/1.webp') }}" alt="{{ $blog->title }}" class="img-fluid">
        @endif
        <div class="blog-date">
            <span class="day">{{ $blog->created_at->format('d') }}</span>
            <span class="month">{{ $blog->created_at->format('M') }}</span>
        </div>
    </div>
    <div class="blog-content">
        <div class="blog-meta">
            <span class="author">
                <i class="twi-user"></i> {{ $blog->author ?? 'Admin' }}
            </span>
            <span class="category">
                <i class="twi-folder"></i> {{ $blog->category->name ?? 'General' }}
            </span>
        </div>
        <h4 class="blog-title">
            <a href="{{ route('website.blog.show', $blog->slug) }}">{{ $blog->title }}</a>
        </h4>
        <p class="blog-excerpt">
            {{ Str::limit(strip_tags($blog->content), 120) }}
        </p>
        <div class="blog-footer">
            <a href="{{ route('website.blog.show', $blog->slug) }}" class="read-more">
                Read More <i class="twi-arrow-right"></i>
            </a>
            <div class="blog-stats">
                <span class="views">
                    <i class="twi-eye"></i> {{ $blog->views ?? 0 }}
                </span>
                <span class="comments">
                    <i class="twi-comment"></i> {{ $blog->comments_count ?? 0 }}
                </span>
            </div>
        </div>
    </div>
</div>

<style>
.blog-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.blog-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.blog-image {
    position: relative;
    overflow: hidden;
}

.blog-image img {
    width: 100%;
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s;
}

.blog-card:hover .blog-image img {
    transform: scale(1.05);
}

.blog-date {
    position: absolute;
    top: 20px;
    left: 20px;
    background: #007bff;
    color: white;
    padding: 10px;
    border-radius: 8px;
    text-align: center;
    min-width: 60px;
}

.blog-date .day {
    display: block;
    font-size: 20px;
    font-weight: 700;
    line-height: 1;
}

.blog-date .month {
    display: block;
    font-size: 12px;
    text-transform: uppercase;
    margin-top: 2px;
}

.blog-content {
    padding: 25px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.blog-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 15px;
    font-size: 14px;
    color: #666;
}

.blog-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.blog-title {
    margin-bottom: 15px;
    font-size: 18px;
    line-height: 1.4;
}

.blog-title a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s;
}

.blog-title a:hover {
    color: #007bff;
}

.blog-excerpt {
    color: #666;
    line-height: 1.6;
    margin-bottom: 20px;
    flex-grow: 1;
}

.blog-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.read-more {
    color: #007bff;
    text-decoration: none;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: color 0.3s;
}

.read-more:hover {
    color: #0056b3;
}

.blog-stats {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #999;
}

.blog-stats span {
    display: flex;
    align-items: center;
    gap: 3px;
}

@media (max-width: 768px) {
    .blog-content {
        padding: 20px;
    }
    
    .blog-footer {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .blog-stats {
        gap: 10px;
    }
}
</style>