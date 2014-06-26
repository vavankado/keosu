/************************************************************************
	Keosu is an open source CMS for mobile app
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
app.controller('menu_gadgetController', function ($scope, $http,$location) {
	$scope.init = function (host, param, page, gadget, zone) {
		$scope.page = page;
		$http.get('data/' + gadget + '.json').success( function (data) {
			var pages = [];
			for (var i = 0; i < data.pages.length; i++) {
				pages[i] = {name :data.pages[i].name.replace(' ',''), ico : data.pages[i].icon};
			}
			$scope.pages = pages;
		});
	};
	
	// @see https://stackoverflow.com/questions/12592472/how-to-highlight-a-current-menu-item-in-angularjs
	$scope.getClass = function(page) {
		return $location.path() == "/Page/"+page ? "active" : ""
	}
});
