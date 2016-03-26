$(document).ready(function(){
		$(".admineditable").prepend(function(){
				var page = $(this).attr('page');
				var lang = $('html')[0].lang;
				return '<a class="option_over" href="/'+lang+'/configs/edithtmlpage/'+page+'">edit</a>';
		});
			
			// '<a class="option_over" href="/'.$('html')[0].lang.'/configs/edithtmlpage/">edit</a>');
		
			
tinymce.init({
    selector: "textarea.tinymce",
    theme: "modern",
    plugins: [
        "advlist autolink lists link image charmap print preview hr anchor pagebreak",
        "searchreplace wordcount visualblocks visualchars code fullscreen",
        "insertdatetime media nonbreaking save table contextmenu directionality",
        "emoticons template paste textcolor colorpicker textpattern jbimages"
    ],
    toolbar1: " styleselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent ",
    toolbar2: "link image media jbimages | preview undo redo | code ",
    image_advtab: false,
    menubar: false,
    relative_urls: false,
    remove_script_host: false,
    document_base_url: "https://housingnotprofit.org/"
});
		
});