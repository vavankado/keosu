/************************************************************************
 Pockeit is an open source CMS for mobile app
 Copyright (C) 2013  Vincent Le Borgne

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as
 published by the Free Software Foundation, either version 3 of the
 License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 ************************************************************************/



//Main function
app.controller('keosu-calendarController', function ($rootScope, $scope, $http, $sce, usSpinnerService, cacheManagerService) {

	$http.defaults.headers.post["Content-Type"] = "application/x-www-form-urlencoded";

	$scope.parts = function (isList, isEvent) {
		$scope.isList = isList;
		$scope.isEvent = isEvent;
	};
	$rootScope.previous = function () {
		$rootScope.previousButton = false;
		$scope.slide="fadeIn";
		$scope.parts(true, false);
		
	};
	$scope.next = function(){
		if(!$scope.isLastPage){
			$scope.isFirstPage = true;
			$scope.isLastPage = true;
			$scope.slide="slideInRight";
			$scope.activePage++;
			$scope.getPage($scope.activePage,true);
		}
	};
	$scope.previous = function(){
		if(!$scope.isFirstPage){
			$scope.isFirstPage = true;
			$scope.isLastPage = true;
			$scope.slide="slideInLeft";
			$scope.activePage--;
			$scope.getPage($scope.activePage,true);
		}
	};
	$scope.open = function (page) {
		$rootScope.previousButton = true;
		$scope.event = page;
		$scope.parts(false, true);
		var map=$scope.initialize();
		map.setZoom($scope.param.gadgetParam.zoom);
		google.maps.event.trigger($("#map_canvas")[0], 'resize');
		var latitudeAndLongitude = new google.maps.LatLng($scope.event.lat,$scope.event.lng);
		map.setCenter(latitudeAndLongitude);

		//Init POI marker
		markerOne = new google.maps.Marker({
			position: latitudeAndLongitude,
			title: "event",
			map: map
		});

		window.setTimeout(function(){
			google.maps.event.trigger($("#map_canvas")[0], 'resize');
			map.setCenter(latitudeAndLongitude);
		},100);

		$scope.parts(false, true, $scope);
	};

	$scope.addToCalendar = function(eventObj){
		if(window.plugins.calendar==null){
			alert("not available on desktop")
		}else{
			startDate = new Date(parseInt(eventObj.datems));
			startDate = new Date(parseInt(eventObj.datems)+25000);
			//TODO Fix this
			$scope.successCal = function() { alert("Success: " + JSON.stringify("Event Added!")); };
			$scope.errorCal = function(message) { alert("Error: " + JSON.stringify(message)); };

			window.plugins.calendar.createEvent(eventObj.name,eventObj.place,eventObj.name,startDate,startDate,$scope.successCal,$scope.errorCal);
		}
	}

	$scope.more = function(){
		$scope.activePage++;
		$scope.getPage($scope.activePage,false);
	}
	//Init google gadget
	$scope.initialize=function() {
		var mapOptions = {
			center: new google.maps.LatLng(47.21677,-1.553307),
			zoom: 3,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(document.getElementById("map_canvas"),mapOptions);
		return map;
	}
	/*
	 * @pageNum : page number requested.
	 * @resetPages : if true, clean the array pages.
	 */
	$scope.getPage = function(pageNum,resetPages){
		if(resetPages){
			$scope.pages = [];
		}
		usSpinnerService.spin('spinner'); // While loading, there will be a spinner
		cacheManagerService.get($scope.param.host+'service/gadget/calendar/'+$scope.param.gadgetId+'/'+pageNum+'/json').success(function(data) {
			usSpinnerService.stop('spinner');
			$scope.isFirstPage = data.isFirst;
			$scope.isLastPage = data.isLast;
			start = $scope.pages.length;
			console.log("Data lenght"+data.data.length);
			for (i = 0; i < data.data.length; i++) {
				$scope.pages[start+i] = data.data[i];
				$scope.pages[start+i].id = $sce.trustAsHtml(decodedContent(data.data[i].id));
				$scope.pages[start+i].name = $sce.trustAsHtml(decodedContent(data.data[i].name));
				$scope.pages[start+i].date = $sce.trustAsHtml(decodedContent(data.data[i].date));
			}
		});
	}
	$scope.init = function (params){
		$rootScope.previousButton = false;
		console.log("init calendar gadget");
		$scope.slide="fadeIn";
		$scope.param = params;
		$scope.pages = new Array();
		$scope.parts(true, false);
		$scope.activePage=0;
		$scope.isFirstPage = true;
		$scope.isLastPage = true;
		$scope.getPage($scope.activePage,true);
	};
});

