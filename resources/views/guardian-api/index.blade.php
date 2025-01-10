@extends('layouts.app')
@section('title', 'Guardian API News')
@section('content')
    <center>Guardian API News Feed</center>

    <div class="row">

        <div class="col-12">
            <form id="updateArticlesForm" class="row" method="post" action="{{ route('guardianNewsApi.updateDatabaseRecords') }}"
                autocomplete="off">

                @csrf
                <div class="col-12">
                    <div id="errorMessages" class="alert alert-danger" style="display: none;"></div>
                    <div id="successMessages" class="alert alert-success" style="display: none;"></div>

                </div>
                <div class="col-md-3  col-xs-12">
                    <div class="form-group">
                        <label for="">How many records?</label>
                        <input type="text" class="form-control" placeholder="100" name="fetch_records">
                    </div>
                </div>
                <div class="col-lg-2 col-xs-12">
                    <div class="form-group">
                        <label for="">Sort By</label>
                        <select name="sort_by" id="" class="form-control">
                            <option value="">Click to select</option>
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                            <option value="relevance">Most Relevant</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 col-xs-12">
                    <div class="form-group">
                        <label for="">Randomize</label>
                        <select name="randomize" id="" class="form-control">
                            <option value="">Click to select</option>
                            <option value="true">True</option>
                            <option value="false">False</option>
                        </select>
                    </div>
                </div>
                <div class="col-xs-12 col-md-2">
                    <div class="form-group">
                        <label for="">Select action</label> <br>
                        <button type="submit" class="btn btn-success">Update Articles</button>
                        <a href="{{ route('guardianNewsApi.cleanDatabase') }}"
                            onclick="return confirm('Are you sure to delete Guardian  API articles?')" id="clean-database"
                            class="btn btn-danger">Clean Database</a>
                    </div>
                </div>
            </form>
        </div>
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
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
                    url: '{{ route('guardianNewsApi.getAllArticles') }}',
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
                        <p class="card-text">Pubblished at: ${article.published_at}</p>
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
                    url: '{{ route('guardianNewsApi.updateDatabaseRecords') }}',
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
