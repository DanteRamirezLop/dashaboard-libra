@extends('layouts.app')
@section('title', __('cms::lang.cms'))

@section('content')

@include('cms::layouts.nav')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>
        @if($post_type == 'page')
            @lang('cms::lang.add_page')
        @elseif($post_type == 'testimonial')
            @lang('cms::lang.add_testimonial')
        @elseif($post_type == 'blog')
            @lang('cms::lang.add_blog')
        @endif
    </h1>
</section>
<!-- input label text based on post type -->
@php
    if (in_array($post_type, ['blog', 'page'])) {
        $title_label = __('cms::lang.title');
        $content_label = __('cms::lang.content');
        $feature_image_label = __('cms::lang.feature_image');
    } elseif (in_array($post_type, ['testimonial'])) {
        $title_label = __('user.name');
        $content_label = __('cms::lang.testimonial');
        $feature_image_label = __('cms::lang.user_image');
    }
@endphp
<!-- Main content -->
<section class="content">
    {!! Form::open(['action' => '\Modules\Cms\Http\Controllers\CmsPageController@store', 'id' => 'create_page_form', 'method' => 'post', 'files' => true]) !!}
        <input type="hidden" name="type" value="{{$post_type}}">
        <div class="row">
            <div class="col-md-9">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('url','Nombre de la URL:*' )!!}
                                {!! Form::text('url', null, ['class' => 'form-control', 'required','id'=>'url' ]) !!}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('slug','URL:' )!!}
                                {!! Form::text('slug', null, ['class' => 'form-control disabled_input', 'required','id'=>'slug' ]) !!}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('title', 'Titulo del articulo:*' )!!}
                                {!! Form::text('title', null, ['class' => 'form-control', 'required' ]) !!}
                            </div>
                        </div>
						<div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('tags', 'Tags' . ':*' )!!}
                                {!! Form::text('tags', null, ['class' => 'form-control', 'required' ]) !!}
                            </div>
                        </div>
						<div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('meta_description', 'Meta descripción' . ':*' )!!}
                                {!! Form::text('meta_description', null, ['class' => 'form-control', 'required' ]) !!}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('content', $content_label . ':*' )!!}
                                {!! Form::textarea('content', null, ['class' => 'form-control' ]) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-3">
                @component('components.widget', ['class' => 'box-solid'])
                  <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                {!! Form::label('feature_image', $feature_image_label . ':') !!}
                                {!! Form::file('feature_image', ['id' => 'feature_image', 'accept' => 'image/*']); !!}
                                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])</p></small>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::label('priority', __('cms::lang.priority') . ':' )!!}
                                {!! Form::number('priority', null, ['class' => 'form-control' ]) !!}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <br>
                                <label>
                                  {!! Form::checkbox('is_enabled', 1, true, ['class' => 'input-icheck']); !!} <strong>@lang('cms::lang.is_enabled')</strong>
                                </label> 
                            </div>
                        </div>
						
						<div class="col-md-12">
                            <div class="form-group">
                                <br>
                                <label for="priority">Business</label>
                                <select name="business_id" class="form-control">
                                    <option disabled>Select business</option>
                                    @foreach($business as $busines)
                                        <option value="{{$busines->id}}">{{$busines->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
						
						
                    </div>
                @endcomponent
            </div>
        </div>
        <!-- TODO:include add SEO -->
        <div class="row">
            <div class="col-md-12 text-center">
                <button type="submit" class="btn btn-primary submit-btn btn-lg">@lang('messages.submit')</button>
            </div>
        </div>
    {!! Form::close() !!}
</section>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            init_tinymce_blog('content',null);

            var img_fileinput_setting = {
                showUpload: false,
                showPreview: true,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
                previewSettings: {
                    image: { width: 'auto', height: 'auto', 'max-width': '100%', 'max-height': '100%' },
                },
            };
            $('#feature_image').fileinput(img_fileinput_setting);

            $("form#create_page_form").validate({
                submitHandler: function(form, e) {
                    if ($('form#create_page_form').valid()) {
                        window.onbeforeunload = null;
                        //if meta des length is 0;extract from content and use it as meta description
                        if (
                            $("textarea#meta_description") &&
                            (
                                $("textarea#meta_description").val().length == 0
                            )
                        ) {
                           let meta_description = tinyMCE.get('content').getContent({format : 'text'});
                            $("textarea#meta_description").val(meta_description.substring(0, 160));
                        }
                        let post_submit_btn = Ladda.create(document.querySelector('.submit-btn'));
                        form.submit();
                        post_submit_btn.start();
                    }
                }
            });
            //display notification before if any data is changed
            __page_leave_confirmation('#create_page_form');
        });

        function slugify(text, maxLength = 100) {
            if (!text) return '';
            let slug = text.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            slug = slug.toLowerCase();
            slug = slug.replace(/[^a-z0-9\s-]/g, '');
            slug = slug.trim().replace(/[\s-]+/g, '-');
            slug = slug.replace(/^-+|-+$/g, '');
            if (maxLength && slug.length > maxLength) slug = slug.slice(0, maxLength).replace(/-+$/,'');
            return slug;
        }

        $('#url').on('input', function() {
            $('#slug').val(slugify($(this).val(), 150));
        });

    </script>
@endsection