tinymce.init({
    selector: "textarea.tinymce",
    theme: "modern",
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor colorpicker textpattern "
    ],
    toolbar1: " styleselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent ",
    toolbar2: "link image media | preview undo redo | code ",
    image_advtab: false,
            menubar: false,
});


