@extends('default-views.component.base')
@section('title','今日更新')

<style>
    .today-albums .pic img {
        display: block;
        max-width: 100%;
        height: 260px;
        margin: 0px auto;
        object-fit: cover;
    }

    .today-albums .pic-count {
        top: 0;
        right: 0;
    }

    .today-albums .pagination {
        display: inline-flex;
    }
</style>
@section('content')
    <div class="today-albums mt-3">
        <h3 class="mt-4 mb-3">最近更新 </h3>
        <div class="row">
            @foreach($today_albums as $album)
                <figure class="pic-item col-6 col-md-2 pl-1 pr-1" itemscope itemtype="http://schema.org/ImageObject">
                    <a class="pic d-block position-relative" href="{{url('/album/'.$album->id)}}"
                       title="{{$album->title}}" itemprop="url">
                        <img class="figure-img lazyload"
                             src="/images/loading.gif"
                             data-src="{{$album->cover}}"
                             alt="{{$album->title}}">
                        <span class="position-absolute pic-count badge badge-secondary m-2 p-1">
                            {{$album->pic_count}}P
                        </span>
                    </a>
                    <figcaption class="figure-caption pic-title text-truncate m-2 mt-0 mb-0">
                        <a class="text-secondary" href="{{url('/album/'.$album->id)}}"
                           title="{{$album->title}}">{{$album->title}}</a>
                    </figcaption>

                    <meta itemprop="name" content="{{$album->title}}">
                    <meta itemprop="image" content="{{$album->cover}}">
                    <meta itemprop="contentUrl" content="{{$album->cover}}">
                    <meta itemprop="datePublished" content="{{$album->created_at}}">
                </figure>
            @endforeach
        </div>

        <div class="mt-4 mb-4">
            {{$today_albums->onEachSide(1)->links()}}
        </div>
    </div>
@endsection