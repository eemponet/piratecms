var lang = $('html')[0].lang;
$(document).ready(function(){
		$( ".datepicker-ui" ).datepicker({
				inline: true,
				changeMonth: true,
				changeYear: true,
				dateFormat: "yy-mm-dd",
		});
		$( ".datepicker-future-ui" ).datepicker({
				inline: true,
				changeMonth: true,
				changeYear: true,
		});
		
		/*$(".translate").prepend(function(){
		var page = $(this).attr('translate');
		var lang = $('html')[0].lang;
		return '<a class="option_over" href="/'+lang+'/configs/translate/'+page+'">translate</a>';
		})*/;
		
});

/** scroll more **/
$(document).ready(function(){
		
		
		// $('.imageupload').picEdit();
		
		
		
		$('#social_networks .more').click(function(e)
			{
				loadmore('#social_networks');
				e.preventDefault();
				// $(this).hide();
				return;
			});
		
		
		$('#rss .more').click(function(e)
			{
				loadmore('#rss');
				e.preventDefault();
				// $(this).hide();
				return;
    		});
    	
    	$('#members .more').click(function(e)
    		{
    			loadmore('#members');
    			e.preventDefault();
    			// $(this).hide();
    			return;
    		});
    	
    	$('.loadmore ').click(function(e)
    		{
    			targetDiv = $(this).children('input[name=div]').val();
    			console.log(targetDiv);
    			loadmore(targetDiv);
    			e.preventDefault();
    			// $(this).hide();
    			return;
    		});
    	
    	$('#rss .newpage').removeClass('newpage');
    	$('#social_networks .newpage').removeClass('newpage');
    	$('#members .newpage').removeClass('newpage');
    	
    	$('.confirmation_link').click(function(e){
    			return confirm('Are you sure?');
    	});
});

$(window).scroll(function()
	{
		if($(window).scrollTop() == $(document).height() - $(window).height())
		{
			console.log('loading more....');
			loadmore('#rss');
			loadmore('#social_networks');
			loadmore('#members');
			loadmore('#articles');
		}
	});

function loadmore(target_div){
	
	var input_pagenr = $(target_div+' input[name=page]');
	var total_pages = $(target_div+' input[name=total_pages]').val();
	var p1 = $(target_div+' input[name=p1]').val();
	var url_action = $(target_div+' input[name=url]').val();
	
	var nrpage_to_load = input_pagenr.val();
	
	
	// console.log(target_div,nrpage_to_load,total_pages);
	
	
	
	nrpage_to_load =  parseInt(nrpage_to_load)+1;
	
	if(nrpage_to_load == total_pages)
	{		
		// $(target_div+' .more').hide();
		$(target_div+' .more').html("<p>No more results</p>");
	}
	
	if(isNaN(nrpage_to_load))
	{
		return;
	}
	
	var url = '/'+lang+'/'+url_action+'/'+nrpage_to_load;
	if(p1 !== undefined){
		url = url+'/'+p1;
	}
	$.get( url, function( data ) {
			$(target_div+'_content').append(data);
			
			$(target_div+' .newpage').hide();
			$(target_div+' .newpage').fadeIn('slow');
			$(target_div+' .newpage').removeClass('newpage');
			input_pagenr.val(nrpage_to_load);
			
	});
	
}
var info;
/** MAP **/
$(document).ready(function(){
		if(jQuery("#join_leafletmap").length){
			
			
			var coords = [50.108872,8.7034681];
			var coordsLatLng = new L.LatLng(coords[0], coords[1]);
			var map = new L.Map('join_leafletmap', {center: coordsLatLng, zoom: 5,maxZoom:9,scrollWheelZoom:false});
			// var osm = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
			// L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png
			L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
					maxZoom: 18,
					attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
					'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
					'Imagery &copy; <a href="http://mapbox.com">Mapbox</a>',
					id: 'examples.map-i875mjb7'
			}).addTo(map);
			
			// map.addLayer(osm);
			// map.addControl(new L.Control.Layers( {'OSM':osm}));
			
			var marker = L.marker(coords);
			var circle = L.circle(coords, 100, {
					color: 'red',
					fillColor: '#f03',
					fillOpacity: 0.5
			});
			var firstclick = false;
			function onMapClick(e) {
				circle.setLatLng(e.latlng);
				marker.setLatLng(e.latlng);
				jQuery("input[name=gps_coords]").val(JSON.stringify(e.latlng));
				if(!firstclick){
					circle.addTo(map);
					marker.addTo(map);
					firstclick = true;
				}
				/*popup
				.setLatLng(e.latlng)
				.setContent("You clicked the map at " + e.latlng.toString())
				.openOn(map);*/
			}
			
			map.on('click', onMapClick);
			
    		
    		var value = jQuery("input[name=gps_coords]").val();
    		// // var value = 'xxx';
    		// console.log(value);
    		// console.log('fuuuu',value.length,'buuu');
    		if(value !== undefined && value.length > 1){
    			var setcoords = JSON.parse(value);
    			circle.setLatLng(setcoords);
    			marker.setLatLng(setcoords);
    			map.panTo(setcoords);
    			if(!firstclick){
    				circle.addTo(map);
    				marker.addTo(map);
    				firstclick = true;
    			}
    		}
    		// jQuery("input#field_4").attr("disabled","disabled");
    		
    	}
    	
    	if(jQuery("#view_leafletmap").length){
			console.log('oi');
			
			var coords = [50.108872,8.7034681];
			var coordsLatLng = new L.LatLng(coords[0], coords[1]);
			var map = new L.Map('view_leafletmap', {center: coordsLatLng, zoom: 5,maxZoom:5 ,scrollWheelZoom:false,dragging: false,zoomControl: false});
			// var osm = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
			// L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
			L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
					maxZoom: 18,
					attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
					'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
					'Imagery &copy; <a href="http://mapbox.com">Mapbox</a>',
					id: 'examples.map-i875mjb7'
			}).addTo(map);
			
			// map.addLayer(osm);
			// map.addControl(new L.Control.Layers( {'OSM':osm}));
			
			var marker = L.marker(coords);
			var circle = L.circle(coords, 100, {
					color: 'red',
					fillColor: '#f03',
					fillOpacity: 0.5
			});
			
			// circle.setLatLng(e.latlng);
			// marker.setLatLng(e.latlng);
			// jQuery("input[name=gps_coords]").val(JSON.stringify(e.latlng));
			circle.addTo(map);
			marker.addTo(map);
			// var firstclick = false;
			// function onMapClick(e) {
			// circle.setLatLng(e.latlng);
			// marker.setLatLng(e.latlng);
			// jQuery("input[name=gps_coords]").val(JSON.stringify(e.latlng));
			// if(!firstclick){
			// 	circle.addTo(map);
			// 	marker.addTo(map);
			// 	firstclick = true;
			// }
			/*popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(map);*/
			// }
			
			// map.on('click', onMapClick);
			
    		
    		var value = jQuery("input[name=gps_coords]").val();
    		// // var value = 'xxx';
    		// console.log(value);
    		// console.log('fuuuu',value.length,'buuu');
    		if(value !== undefined && value.length > 1){
    			var setcoords = JSON.parse(value);
    			circle.setLatLng(setcoords);
    			marker.setLatLng(setcoords);
    			map.panTo(setcoords);
    			if(!firstclick){
    				circle.addTo(map);
    				marker.addTo(map);
    				firstclick = true;
    			}
    		}
    		// jQuery("input#field_4").attr("disabled","disabled");
    		
    	}
    	
    	
    	if(jQuery("#fullmap_leafletmap").length){
    		var url = jQuery("#fullmap_leafletmap").attr('url');
    		
    		
    		
    		var mapconfig = jQuery("#fullmap_leafletmap").attr('mapconfig');
    		
    		// var coords = [47.68,3.87];
    		var coords = [44.637,4.219];
    		var zoomSetting = 4;
    		if(mapconfig == 2){
    			coords = [50.21,-10.72];
    			// coords = [36.462,1.75];
    			zoomSetting = 3;
    		}
    		
    		var coordsLatLng = new L.LatLng(coords[0], coords[1]);
    		
    		
    		
    		var map = L.map('fullmap_leafletmap', {center: coordsLatLng, zoom: zoomSetting,maxZoom:9,zoomControl: false,scrollWheelZoom:false});
    		
    		// ,scrollWheelZoom:false
    		// var osm = new L.TileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png');
    		// L.tileLayer('https://{s}.tiles.mapbox.com/v3/{id}/{z}/{x}/{y}.png', {
    		L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
    				maxZoom: 18,
    				attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
    				'<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
    				'Imagery © <a href="http://mapbox.com">Mapbox</a>',
    				id: 'examples.map-i875mjb7'
    		}).addTo(map);
    		
    		// map.addControl(new L.Control.Layers( {'OSM':tiles, 'Sattelite':ggl}));
    		
    		// var markers = L.markerClusterGroup({ 
    		// 		chunkedLoading: true,
    		// 		animateAddingMarkers: true,
    		// });
    		var firstmemberhtml = '';
    		if(url !== undefined){
    			url = '/'+lang+'/coalition/map';
    		
    			$.get( url, function( data ) {
    					
    					items = JSON.parse(data);
    					
    					for (var i = 0; i < items.length; i++) {
    						
    						var marker = createMarker(items[i],i);
    						
    						if(marker !== undefined){
    							marker.addTo(map);
    						}
    						if(firstmemberhtml == ''){
    							firstmemberhtml = items[i].html;
    						}
    					}
    					
    			});
    			
    		}else{
    			var data = jQuery("#fullmap_leafletmap").attr('data');
    			
    			items = JSON.parse(data);
    			
    			for (var i = 0; i < items.length; i++) {
    				
    				var marker = createMarker(items[i],i);
    				
    				if(marker !== undefined){
    					marker.addTo(map);
    				}
    				if(firstmemberhtml == ''){
    					firstmemberhtml = items[i].html;
    				}
    			}
    			
    			var data2 = jQuery("#fullmap_leafletmap").attr('data2');
    			if(data2 != null){
    				items = JSON.parse(data2);
    				
    				for (var i = 0; i < items.length; i++) {
    					
    					var marker = createMarker(items[i],i);
    					
    					if(marker !== undefined){
    						marker.addTo(map);
    					}
    					if(firstmemberhtml == ''){
    						firstmemberhtml = items[i].html;
    					}
    				}
    			}
    		}
    		
    		
    		
    		// var info = L.control();
    		
    		info = L.control({
    				position : 'topleft'
    		});
    		info.onAdd = function (map) {
    			this._div = L.DomUtil.create('div', 'map-info'); // create a div with a class "info"
    			this.update(firstmemberhtml);
    			return this._div;
    		};
    		
    		// method that we will use to update the control based on feature properties passed
    		info.update = function (html) {
    			this._div.innerHTML = '<div class="inner_map">'+html+'</div>';
    			// this._div.innerHTML = '<h4>US Population Density</h4>' + props.html + (props ?
    			// '<b>' + props.updateInfo + '</b><br />' + props.updateInfo + ' people / mi<sup>2</sup>'
    			// : 'Hover over a state');
    		};
    		
    		
    		
    		info.addTo(map);
    		
    		
    		
    	}
    	// $( ".checkvisibility" ).append( "<p>Test</p>" );
    	$.each($('.checkvisibility'), function( obj){
    			obj.parent().prepend("<a id='hideBtn' class='genericon genericon-hide' alt='hide' title='hide' href='#' onclick='console.log(\"visisss\");'>&nbsp;</a>	");
    	});
    	// parent().prepend("<a id='hideBtn' class='genericon genericon-hide' alt='hide' title='hide' href='#' onclick='console.log(\"visisss\");'>&nbsp;</a>	");
});

function createMarker(item,i){
	
	var coords = JSON.parse(item.gps_coords);
	if(coords == null){
		return;
	}
	
	var marker = L.marker(L.latLng(coords.lat, coords.lng));
	var title = item.name;
	var htmlpopup = "";
	
	if( item.marker != null ){
		
		var myIcon = L.icon({
				iconUrl: "/"+item.marker,
				iconSize: [32, 32],
		});
		marker = L.marker(L.latLng(coords.lat, coords.lng), { title: title,icon: myIcon });
		
	}
	// htmlpopup = htmlpopup+ "<a href='"+item.url+"'><img class='thumbnail' src='/"+item.name+"'></a>";
	
	
	htmlpopup = item.html;
	
	// console.log("lll",i);
	// if(i == '0')
	// {
		// console.log("setting...!",i,htmlpopup);
		// $('.inner_map').html(htmlpopup);
	// }
	
	marker.on( "mouseover", updateInfo ,{html: htmlpopup});
	marker.on( "click", openmemberpage ,{url: item.url});
    							
	return marker;
	
}
function updateInfo(html)
{
	
	info.update(this.html);
}
function openmemberpage()
{
	window.location = this.url;
}

