@extends('layouts.app')
@section('title', 'Guardian News API')
@section('content')
    <center>All API News Feed</center>

     
    <div class="row my-3">
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div>
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
            <input type="text" id="searchQuery" class="form-control" placeholder="Search for articles...">
        </div>
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div>

    </div>
    <hr>
    <div id="articleList" class="row"></div>
    <div id="articleCounter" class="mb-3">Available articles: 0</div>
    <div id="loader" style="display: none; text-align: center; margin-top: 20px;">
        Loading more articles...
    </div>
@endsection
@section('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            let page = 1;
            const pageSize = 30;
            let hasMoreArticles = true;
            let totalLoadedArticles = 0;

            loadArticles(page, pageSize);

            $('#searchQuery').on('input', function() {
                const query = $(this).val();
                page = 1;
                totalLoadedArticles = 0;
                $('#articleList').empty();
                hasMoreArticles = true;
                loadArticles(page, pageSize, query);
            });

            function loadArticles(page, pageSize, query = '') {
                if (!hasMoreArticles) return;

                $.ajax({
                    url: '{{ route('allArticles') }}',
                    type: 'GET',
                    data: {
                        page: page,
                        pageSize: pageSize,
                        searchQuery: query,
                    },
                    beforeSend: function() {
                        $('#loader').text('Loading articles...').show();
                    },
                    success: function(response) {
                        if (response.articles && response.articles.length > 0) {
                            displayArticles(response.articles);
                            totalLoadedArticles += response.articles.length;
                            $('#articleCounter').text(`Loaded articles: ${totalLoadedArticles}`);
                        } else {
                            hasMoreArticles = false;
                            $('#loader').text('No more articles to load.').show();
                        }
                    },
                    error: function() {
                        alert('Failed to load articles.');
                    },
                    complete: function() {
                        $('#loader').hide();
                    },
                });
            }

            function displayArticles(articles) {
                articles.forEach((article) => {
                    const articleHtml = `
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="${article.url_to_image}" class="card-img-top" alt="${article.title}">
                    <div class="card-body card-body-height">
                        <h5 class="card-title">${article.title}</h5>
                        <p class="card-text">${article.description}</p>
                    </div>
                </div>
            </div>`;
                    $('#articleList').append(articleHtml);
                });
            }

            $(window).scroll(function() {
                if (hasMoreArticles && $(window).scrollTop() + $(window).height() >= $(document).height() -
                    100) {
                    page++;
                    loadArticles(page, pageSize, $('#searchQuery').val().trim());
                }
            });



        });
    </script>

@endsection
