@push('css')

{{--    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.css" rel="stylesheet">--}}
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
@endpush

@push('js')

{{--    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote-bs4.min.js"></script>--}}
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>

    <script>
        $(function () {
            $('#id-field-content').summernote({
                minHeight: 200,
                prettifyHtml: true,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['codeview']],
                ],

                onCreateLink: function (originalLink) {
                    if (originalLink.includes('unsubscribe_url')) {
                        return '@{{unsubscribe_url}}';
                    }

                    if (originalLink.includes('webview_url')) {
                        return '@{{webview_url}}';
                    }

                    return /^([A-Za-z][A-Za-z0-9+-.]*\:|#|\/)/.test(originalLink)
                        ? originalLink : 'http://' + originalLink;
                },
                callbacks: {
                     onImageUpload: function(files) {
                     // upload image to server and create imgNode...
                     uploadImage(files[0])
                    //  $summernote.summernote('insertNode', imgNode);
                    }
                 }
            });
        });

        function uploadImage(image) {
            var data = new FormData();
            data.append("image", image);
            $.ajax({
                url: "https://marketing.socialconnector.io/api/upload-image",
                cache: false,
                contentType: false,
                headers: {
                    'Accept': 'application/json',
                },
                processData: false,
                data: data,
                type: "post",
                success: function(url) {
                    var image = $('<img>').attr('src', url);
                    $('#id-field-content').summernote("insertNode", image[0]);
                },
                error: function(data) {
                    console.log(data);
                }
            });
        }
    </script>
@endpush
