<?php
/************************************************************************
 Keosu is an open source CMS for mobile app
Copyright (C) 2014  Vincent Le Borgne, Pockeit

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
namespace Keosu\CoreBundle\Controller;

use Keosu\CoreBundle\KeosuExtension;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DataModelServiceController extends Controller {

	/**
	 * REST service returning the list of all data models
	 */
	public function listAction($format) {
		return $this
				->render(
						'KeosuCoreBundle:Content/Service:list.' . $format
								. '.twig', array('contents' => array_keys(KeosuExtension::$dataModelList)));
	}
}
