@extends('layouts.app')
@section('title', 'News API')
@section('content')
    <center>ALl News APi for News</center>
    <div class="row">
        <div class="col-12">

            @if (isset($headlines['error']))
                Unable to retrieve <strong>top headlines</strong> for now
            @endif

            @if (isset($headlines['articles']) && count($headlines['articles']) > 0)
                <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel">
                    <!-- Indicators -->
                    <div class="carousel-indicators">
                        @foreach (array_chunk($headlines['articles'], 3) as $index => $group)
                            <button type="button" data-bs-target="#newsCarousel" data-bs-slide-to="{{ $index }}"
                                class="{{ $index == 0 ? 'active' : '' }}" aria-current="{{ $index == 0 ? 'true' : '' }}"
                                aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>

                    <div class="carousel-inner">
                        @foreach (array_chunk($headlines['articles'], 3) as $index => $group)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}">
                                <div class="row">
                                    @foreach ($group as $article)
                                        <div class="col-md-4 d-flex align-items-stretch">
                                            <div class="article-container border p-3 rounded shadow-sm">
                                                <img src="{{ $article['urlToImage'] ?? 'https://via.placeholder.com/150x150?text=No+Image' }}"
                                                    alt="{{ $article['title'] }}" class="img-fluid article-image">
                                                <div class="article-content">
                                                    <div class="article-title">{{ $article['title'] }}</div>
                                                    <div class="article-description">{!! $article['description'] !!}</div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            @else
                <p class="text-center">No headlines available at the moment.</p>
            @endif
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-12">
            <p>Lastly updated at {{ Carbon\Carbon::now()->format('Y-m-d H:i a') }}</p>
        </div>
        <div class="col-12">
            <form id="updateArticlesForm" class="row" method="post"
                action="{{ route('newsApi.updateDatabaseRecords') }}" autocomplete="off">

                @csrf
                <div class="col-12">
                    <div id="errorMessages" class="alert alert-danger" style="display: none;"></div>
                    <div id="successMessages" class="alert alert-success" style="display: none;"></div>

                </div>
                <div class="col-lg-2 col-xs-12">
                    <div class="form-group">
                        <label for="">How many records?</label>
                        <input type="text" class="form-control" placeholder="100" name="fetch_records">
                    </div>
                </div>
                <div class="col-lg-3 col-xs-12">
                    <div class="form-group">
                        <label for="">From</label>
                        <input type="date" class="form-control" name="fetch_from">
                    </div>
                </div>
                <div class="col-lg-3 col-xs-12">
                    <div class="form-group">
                        <label for="">To</label>
                        <input type="date" class="form-control" name="fetch_to">
                    </div>
                </div>
                <div class="col-lg-2 col-xs-12">
                    <div class="form-group">
                        <label for="">Sort By</label>
                        <select name="sort_by" id="" class="form-control">
                            <option value="">Click to select</option>
                            <option value="publishedAt">publishedAt</option>
                            <option value="relevancy">relevancy</option>
                            <option value="popularity">popularity</option>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 col-md-2">
                    <div class="form-group">
                        <label for="">Select action</label> <br>
                        <button type="submit" class="btn btn-success">Update Articles</button>
                        <a href="{{ route('newsApi.cleanDatabase') }}"
                            onclick="return confirm('Are you sure to delete News API articles?')" id="clean-database"
                            class="btn btn-danger">Clean Database</a>
                    </div>
                </div>
            </form>
        </div>

    </div>
<hr>
    <div class="row my-3">
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div>
        <div class="col-xs-12 col-sm-8 col-md-8 col-lg-8">
            <input type="text" id="searchQuery" class="form-control" placeholder="Search for articles...">
        </div>
        <div class="col-xs-12 col-sm-2 col-md-2 col-lg-2"></div>

    </div>
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
                    url: '{{ route('newsAPI.preloadArticles') }}',
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

            // enabling loading of more items
            $(window).scroll(function() {
                if (hasMoreArticles && $(window).scrollTop() + $(window).height() >= $(document).height() -
                    100) {
                    page++;
                    loadArticles(page, pageSize, $('#searchQuery').val().trim());
                }
            });


            $('#updateArticlesForm').on('submit', function(e) {
                e.preventDefault();

                var formData = $(this).serialize();
                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: '{{ route('newsApi.updateDatabaseRecords') }}',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    beforeSend: function() {
                        $('button[type="submit"]').prop('disabled', true);
                    },
                    success: function(response) {
                        $('#successMessages').html('Articles updated successfully!').fadeIn();

                        $('#updateArticlesForm')[0].reset();

                        setTimeout(function() {
                            $('#successMessages').fadeOut();
                        }, 5000);

                        $('#articleList').empty();
                        page = 1;
                        totalLoadedArticles = 0;
                        hasMoreArticles = true;
                        loadArticles(page, pageSize);
                    },
                    error: function(xhr, status, error) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessages = '';
                            for (var field in errors) {
                                if (errors.hasOwnProperty(field)) {
                                    errorMessages += '<p>' + errors[field].join('<br>') +
                                        '</p>';
                                }
                            }

                            $('#errorMessages').html(errorMessages).fadeIn();
                        } else {
                            console.log(error);
                            var errorMessage = xhr.responseJSON.error.message ||
                                'Request failed: Unknown error';

                            // var errorMessage = 'Request failed: ' + error;
                            $('#errorMessages').html(errorMessage).fadeIn();
                        }

                        setTimeout(function() {
                            $('#errorMessages').fadeOut();
                        }, 5000);
                    },
                    complete: function() {
                        $('button[type="submit"]').prop('disabled', false);
                    }
                });
            });
        });
    </script>

@endsection
