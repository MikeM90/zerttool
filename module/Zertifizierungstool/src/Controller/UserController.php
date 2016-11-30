<?php
namespace Zertifizierungstool\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zertifizierungstool\Model\User;

/**
 * Dokumentation
 * 
 * @author martin waldmann
 *
 */
class UserController extends AbstractActionController
{
	public function registerAction()
	{
		$user = new User();
		$test = "Testanzeige";
		$user->load('waldma');
		
		return new ViewModel([
				'benutzer' => array($user),
				'title' => array($test),
		]);
	}
}