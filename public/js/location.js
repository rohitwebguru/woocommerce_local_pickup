(function (exports) {
  "use strict";
	var map;
  // The following example creates complex markers to indicate beaches near
  // Sydney, NSW, Australia. Note that the anchor is set to (0,32) to correspond
  // to the base of the flagpole.
  function initMap() {
      var geocoder;          
		map = new google.maps.Map(document.getElementById("local-pick-up-map"), {
          zoom: 5,
          center: {
              lat: 51.0130785,
              lng: -114.1416814,
          },
      });

      geocoder = new google.maps.Geocoder();
      currentLocation(map);
      setMarkers(geocoder, map);
	  hideAllOptions();
	  
  } // Data for the markers consisting of a name, a LatLng and a zIndex for the
  // order in which these markers should display on top of each other.
  function initSelectMap(pos){
		if(!pos) return;
		var map = new google.maps.Map(document.getElementById("local-pick-up-selected-map"), {
          zoom: 15,
          center:pos,
		});
		var marker = new google.maps.Marker({
		  map: map,
		  position:pos
		});
  }
  function hideAllOptions(){
	  let x=document.querySelectorAll("#pickup_location_addresss option[value]");
	  for (let i = 0; i < x.length; i++) {
		x[i].style.display='none';
	  }
  }
  function setMarkers(geocoder, map) {
      var pickup_locations = map_pickup_data.pickup_locations;
      for (var i = 0; i < pickup_locations.length; i++) {
          var pickup = pickup_locations[i];    
		  var pickuplocation = new google.maps.LatLng(pickup.lat,pickup.lng); 		  
          if (pickup.lat && pickup.lng && valid_coords(pickup.lat,pickup.lng)) {
			  if(withInMiles(pickuplocation)){				
				  document.querySelector("option[value='"+pickup.pick_id+"']").style.display='block';
				  document.getElementById('pickup_location_addresss').value=pickup.pick_id;
				  if(i==pickup_locations.length-1){
					  initSelectMap(pickuplocation);
				  }				  
				   var marker = new google.maps.Marker({
					  map: map,
					  position: {
						  lat:pickup.lat,
						  lng:pickup.lng,
					  },
					  pick_id:pickup.pick_id
				  }).addListener("click", function () {
					  document.getElementById('pickup_location_addresss').value=this.pick_id;
					  console.log("lat", this.position.lat());
					  console.log("lng", this.position.lng());
					  console.log("pick_id", this.pick_id);
					  initSelectMap(this.position);
					  jQuery('#kvell_delivery_local_pickup').trigger('click');
				  }); 
			  }
          }else{
            geocoder.geocode({ address: pickup_locations[i].address },makeCallback(pickup,i,pickup_locations));
          }         
      }
      function makeCallback(pickup,i,pickup_locations) {
        var geocodeCallBack = function (results, status) {
              if (status === "OK" && withInMiles(results[0].geometry.location)) {
				  document.querySelector("option[value='"+pickup.pick_id+"']").style.display='block';
				  document.getElementById('pickup_location_addresss').value=pickup.pick_id;
				  initSelectMap(results[0].geometry.location);
					if(i==pickup_locations.length-1){
					  initSelectMap(pickuplocation);
				  }
                  var marker = new google.maps.Marker({
                      map: map,
                      position: results[0].geometry.location,
                      pick_id:pickup.pick_id
                  }).addListener("click", function () {
                      document.getElementById('pickup_location_addresss').value=this.pick_id;
                      console.log("lat", this.position.lat());
                      console.log("lng", this.position.lng());
                      console.log("pick_id", this.pick_id);
					  initSelectMap(this.position);
                      jQuery('#kvell_delivery_local_pickup').trigger('click');
                  });
              }
          }
          return geocodeCallBack;
        }
  }
  function withInMiles(pickuplocation){
		var searchedlocation = new google.maps.LatLng(51.0130785,-114.1416814);   
		if(current_location){			
			searchedlocation=new google.maps.LatLng(current_location.lat,current_location.lng);							
		}
		var distance = google.maps.geometry.spherical.computeDistanceBetween(
			searchedlocation,
			pickuplocation
		);
		if (distance <= 80467.00) return true;
		return false;
  }
  function mapPosCircle(pos){
	  new google.maps.Circle({
		strokeColor: "#FF0000",
		strokeOpacity: 0.8,
		strokeWeight: 2,
		fillColor: "#FF0000",
		fillOpacity: 0.35,
		map,
		center:pos,
		radius: 80467.00
	  });          
	  map.setCenter(pos);
	  map.setZoom(8);
  }
  function currentLocation(map){
    var infoWindow;
    infoWindow = new google.maps.InfoWindow;
    var map=map;
      // Try HTML5 geolocation.
      if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
          var pos = {
            lat: position.coords.latitude,
            lng: position.coords.longitude
          };
          // pos = {
          //   lat: 51.013078,
          //   lng: -114.141681
          // };
          new google.maps.Circle({
            strokeColor: "#FF0000",
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: "#FF0000",
            fillOpacity: 0.35,
            map,
            center:pos,
            radius: 80467.00
          });          
          map.setCenter(pos);
          // infoWindow.setPosition(pos);
          // infoWindow.setContent('Your Location');
          // infoWindow.open(map);
          // map.setCenter(pos);
        }, function() {
			if(current_location){			
				mapPosCircle(current_location);
			}else{
			  handleLocationError(true, infoWindow, map.getCenter(),map);
			}
        });
      } else {
		if(current_location){
			//searchedlocation=currentLocation;
			console.log("cut",current_location);
		}else{
				// Browser doesn't support Geolocation
			handleLocationError(false, infoWindow, map.getCenter(),map);	
		}

      }
  }

  function inrange(min,number,max){
      if ( !isNaN(number) && (number >= min) && (number <= max) ){
          return true;
      } else {
          return false;
      };
  }

  function valid_coords(number_lat,number_lng) {
    if (inrange(-90,number_lat,90) && inrange(-180,number_lng,180)) {
        return true;
    }
    else {
        return false;
    }
  }
  function handleLocationError(browserHasGeolocation, infoWindow, pos,map) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(browserHasGeolocation ?
                          'Error: Your device doesn\'t support geolocation.' :
                          'Error: Your browser doesn\'t support geolocation.');
    infoWindow.open(map);
  }
  exports.initMap = initMap;
  exports.setMarkers = setMarkers;
  exports.mapPosCircle = mapPosCircle;
  exports.initSelectMap = initSelectMap;
})((this.window = this.window || {}));
