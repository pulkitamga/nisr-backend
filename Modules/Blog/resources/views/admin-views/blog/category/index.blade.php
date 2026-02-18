<div class="modal right fade" id="sidebarCategoryModal" tabindex="-1" role="dialog" aria-labelledby="sidebarCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-slideout modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header bg-section">
                <h3 class="mb-0">{{ translate('Category_Setup') }}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="mb-4">
                    @include("blog::admin-views.blog.category.partials._create-category")
                    @include("blog::admin-views.blog.category.partials._edit-category")
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center justify-content-between mb-3">
                            <div class="col-md-4">
                                <h4 class="m-0">{{ translate('Category_List') }}
                                    @if(count($categories) > 0)
                                    <span class="badge badge-info">{{ $categories->total() }}</span>
                                    @endif
                                </h4>
                            </div>
                            <div class="col-md-8">
                                <form action="javascript:" method="POST" id="search-form" class="mb-0 px-1">
                                    @csrf
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input id="datatableSearch" type="search" class="form-control" name="searchValue" placeholder="{{ translate('Search_by_Category_Name') }}">
                                            <div class="input-group-append">
                                                <button class="btn btn-light" type="submit">
                                                    <i class="fi fi-rr-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div id="categories-table">
                            @include("blog::admin-views.blog.category.partials.table-rows")
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>