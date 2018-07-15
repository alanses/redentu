@extends('layouts.app')

@section('content')
    <div class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <photo-component-vue></photo-component-vue>
                </div>
            </div>
        </div>
    </div>


@endsection

<style>
    .news {
        display: flex;
    }

    .image {
        flex: 1 1 25%;
    }

    .image img {
        max-width: 400px;
    }

    .short-description {
        flex: 1 1 75%;
        padding-left: 15px;
    }
</style>