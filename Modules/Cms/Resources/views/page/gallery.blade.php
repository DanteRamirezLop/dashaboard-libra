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
   
        $title_label = 'Gallery';
        $content_label = 'Gallery';
        $feature_image_label = 'Cargar Imagen';

@endphp
<!-- Main content -->
<section class="content">
    {!! Form::open(['action' => '\Modules\Cms\Http\Controllers\CmsPageController@store', 'id' => 'create_page_form', 'method' => 'post', 'files' => true]) !!}
        <input type="hidden" name="type" value="{{$post_type}}">
        <input type="hidden" name="title" value="imagen">
        <input type="hidden" name="content" value="imagen">
        <div class="row">
            <div class="col-md-4">
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="form-group">
                                {!! Form::label('feature_image', $feature_image_label . ':') !!}
                                {!! Form::file('feature_image', ['id' => 'feature_image', 'accept' => 'image/*']); !!}
                                <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)])</p></small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary submit-btn btn-block">@lang('messages.submit')</button>
                @endcomponent
            </div>
            <div class="col-md-8">
                <h2>Gallery</h2>
                <h3>Cantidad de images: {{count($pages)}}</h3>
                <p>Puedes utilizar la ruta de las images para insertarlas en los editores de Blog, Page y Testimonio.</p>  
            </div>
        </div>
    {!! Form::close() !!}
    <div class="row">
 
            @forelse($pages as $page)
                <div class="col-md-2 page-box">
                    <img src="/uploads/cms/{{$page->feature_image}} " alt="Libra International" width="100%" style="height: 150px;">
                    @component('components.widget', ['class' => 'box box-solid', 'title' => $page->title])
                        @slot('tool')
                            <div class="box-tools" style="display: flex;">
                                @if(empty($page->layout))
                                    <button data-href="{{action([\Modules\Cms\Http\Controllers\CmsPageController::class, 'destroy'], [$page->id, 'type' => $post_type])}}" class="btn btn-xs btn-danger delete_page">
                                        <i class="glyphicon glyphicon-trash"></i>
                                    </button>
                                @endif
                            </div>
                        @endslot
                        
                        <p class="text-muted">
                            @lang('lang_v1.added_on'): {{@format_datetime($page->created_at)}}
                        </p>
                        <a class="btn btn-primary btn-block copiar_codigo" data-codigo="{{ENV('APP_URL')}}uploads/cms/{{$page->feature_image}}">Copiar URL</a>
                       
                    @endcomponent
                </div>
                @if($loop->iteration%6 == 0)
                    <div class="clearfix"></div>
                @endif
            @empty
                <div class="col-md-12">
                    <div class="callout callout-info">
                        <h3>
                            <i class="fas fa-exclamation-circle"></i>
                            @lang('cms::lang.not_found_please_add_one')
                        </h3>
                    </div>
                </div>
            @endforelse
    </div>
    <div class="row">
        {!! $pages->appends(request()->query())->links() !!}
    </div>
</section>

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function () {
            init_tinymce('content');

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

            //Delete 
            $(document).on('click', 'button.delete_page', function() {
            var page_box = $(this).closest('.page-box');
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                page_box.remove();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });

        $(".copiar_codigo").click(function (e) {
            e.preventDefault();
            var ele = $(this);
            var codigo = ele.attr("data-codigo");
            navigator.clipboard.writeText(codigo);
            swal({
                title: 'URL copiada',
                icon: 'success',
            });
        })

    })
    </script>
@endsection