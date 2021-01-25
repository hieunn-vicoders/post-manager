<section class="bg-bread">
    <div class="container">
        <nav class="breadcrumb">#
            <a class="breadcrumb-item" href="/">Trang chủ</a>
            <a class="breadcrumb-item" href="">Tin tức</a>

        </nav>
    </div>
</section>
<section class="newsdetails">
    <div class="container">
        <div class="row">
            <div class="col-12 col-12 col-md-6 col-lg-8 col-xl-9">
                <div class="news-post">
                    <div class="line"></div>
                    <div class="date-time">
                        d
                    </div>
                    <div class="content">
                        @if($draft)
                        {!! $draft->payload !!}
                        @else
                        <p> chưa có bài viết</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
