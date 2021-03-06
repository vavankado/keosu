<?php

namespace Keosu\Gadget\MapGadgetBundle\EventListener;

use Keosu\CoreBundle\KeosuEvents;
use Keosu\CoreBundle\Event\GadgetFormBuilderEvent;

use Keosu\Gadget\MapGadgetBundle\KeosuGadgetMapGadgetBundle;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Listener responsible to gadget action
 */
class GadgetListener implements EventSubscriberInterface
{

	private $container;

	public function __construct($container)
	{
		$this->container = $container;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return array(
			KeosuEvents::GADGET_CONF_FORM_BUILD.KeosuGadgetMapGadgetBundle::PACKAGE_NAME => 'onGadgetConfFormBuild',
		);
	}

	public function onGadgetConfFormBuild(GadgetFormBuilderEvent $event)
	{
		$event->setOverrideForm(true);
		$em = $this->container->get('doctrine')->getManager();
		
		//Get list of Point of interest
		$queryPOIList = $em->createQueryBuilder();
		$queryPOIList->add('select','l.id, l.name')
						->add('from', 'Keosu\DataModel\LocationModelBundle\Entity\Location l');
		$poiListTmp=$queryPOIList->getQuery()->execute();
		
		
		//Prepare the list of poi for the form
		$poiList=array();
		foreach($poiListTmp as $poi){
			$poiList[$poi['id']]=$poi['name'];
		}
		
		//Prepare the list of zoom choices
		for ($i = 1; $i <= 17; $i++) {
			$zoomList[$i]=$i;
		}
		
		//Overide form
		$builder = $event->getFormBuilder();
		$builder->add('poiId','choice',array(
						'label' 	=> 'Point of interest',
						'choices'	=> $poiList
					))
					->add('zoom','choice',array(
							'choices'	=> $zoomList,
							'label'		=> 'Zoom level when opening'
					));
		
	}
}

