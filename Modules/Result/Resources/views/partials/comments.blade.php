<ul class="list-unstyled">
    @foreach($comments as $comment)
    <li class="remark-item">
        <button onclick="setComment(`{{ $comment->text }}`)" type="button" class="btn btn-link btn-sm">
            {{ $comment->text }}
        </button>
    </li>
    @endforeach
</ul>
