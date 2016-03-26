$(document).ready(function(){
		$("#activesection").html($("input#title").val());
		$( "body" ).on( "click", function( event ) {
               
                var link = $(event.target);
                if(!link.hasClass("ajaxpagination")){
                   return true;
                }
                 event.preventDefault();
                
				var url = link.attr('href');
				// console.log(url);
				var results = link.parents('.ajaxresult');
				// var normalController = results.attr('normalController');
				// var ajaxController = results.attr('ajaxController');
				
				// url = url.replace(normalController,ajaxController);
                var formu = link.parents('.results');
                var prevurl = formu.attr('url');
                
				url = getUrlTerms(URI(formu.attr('url')),formu.attr('controller'),formu.attr('action'),formu.attr('page'),"pagina",link.attr('page'));
                formu.attr('url',url);
				loadPage(url,results);
				/*$.get( url, function( data ) {
						results.find('.ajaxresult').html(data);
				});*/
				results.attr('url',url);
				
				link.parents().find("li").removeClass('active');
				link.parent().addClass('active');
				
                console.log("novo url:"+url+" antigo:"+prevurl);
                
		});
		$(".results .filters input.autofilter").autofilter();
		/*$(".results .filters input.autofilter").keyup(function(e){
				var btn = $( this );
				// btn.off( e );
				// var url = window.location.pathname;
				var results = $(this).parents('.results');
				
				var uri = URI.expand("/{controller}/{action}/{page}", {
						controller: results.attr('controller'),
						action: results.attr('action'),
						page: results.attr('page')
				});
				
				var term = $(this).val();
				var name = $(this).attr("name");
				
				var uri_prev = URI(results.attr('url'));
				var query = uri_prev.removeSearch(name).query();
				uri = URI(uri.toString()+"?"+query).addSearch(name,term);
				url = $("#base").val()+uri.toString();
				
				$.get( url, function( data ) {
						results.find('.ajaxresult').html(data);
						var totalpages = results.find('.paginated').attr("totalpages");
						results.find(".ajaxpagination li").removeClass('active');
						
						// console.log(url);
						var count = 0;
						results.find(".ajaxpagination li").each(function(){
								count++;
								var a = $(this).find('a');
								// var href = a.attr('href');
								var uri_pages = URI.expand("/{controller}/{action}/{page}", {
										controller: results.attr('controller'),
										action: results.attr('action'),
										page: String(count)
								});
								uri = URI(uri_pages.toString()+"?"+query).addSearch(name,term);
								// href = URI(url+"/"+String(count)).addSearch(name,term);
								
								a.attr('href',$("#base").val()+uri.toString());
								
								$(this).css('display','');
								if(count > totalpages){
									$(this).css('display','none');
								}
						});
						results.find(".ajaxpagination li:first").addClass('active');
						console.log('setting: ',url);
						results.attr('url',url);
						// btn.on( e );
				});
		});*/
		
		$(".results .filters input.autofilter").click(function(e){
				
		});
		$('body').on('click', '.magiceye',function(e){
				
				var a = $(this).parent();
				a.html("");
				
				var url = $(this).attr('href');
				console.log(url);
				e.preventDefault();
				$.get( url, function( data ) {
						a.html(data);
				});
		});
		return true;
});
function loadPage(url, selector){
    
    selector.html("");
    
    //var url = $(this).attr('href');
    //console.log(url);
    
    $.get( url, function( data ) {
            selector.html(data);
    });
}
jQuery.fn.extend({
        
		autofilter: function () {
			var timer;
			
			function ola(){
				console.log('olare');
			}
			function updateResults(){
				
			}
			$(this).on("input paste click",function(e){
					var element_input = $(this);
					clearTimeout(timer);
					timer = setTimeout(function() {
							
							var formu = element_input.parents('.results');
                            var prevurl = formu.attr('url');
                             var value = element_input.val();
                             var name = element_input.attr("name");
                            
                             if(element_input.attr("type") == "checkbox"){
                                 value = 0;
                                 if(element_input.is(':checked')){
                                     value = 1;
                                 }
                                 
                                 // return false;
                             }
							var url = getUrlTerms(URI(formu.attr('url')),formu.attr('controller'),formu.attr('action'),formu.attr('page'),name,value);
                            var results = $('.ajaxresult');
							loadPage(url,results);
                            formu.attr('url',url);
                            
                            console.log("novo url:"+url+" antigo:"+prevurl);
                           
							
					},200);
			});
			
		}
		
});

function getUrlTerms(prevurl,controller,action,page,name,value){
    //var formu = element_input.parents('.results');
    var uri = URI.expand("/{controller}/{action}/{page}", {
            controller: controller,
            action: action,
            page: page
    });
    
    var uri_prev = prevurl; 
    
    if(name != -1){
        var query = uri_prev.removeSearch(name).query();
        uri = URI(uri.toString()+"?"+query).addSearch(name,value);
    }
    url = $("#base").val()+uri.toString();
    return url;
}
