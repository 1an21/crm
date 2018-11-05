<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use FOS\RestBundle\View\View;
class AuthController extends Controller
{
    /**
     * @Route("/register", name="register")
     * @Method({"POST"})
     */
    public function registerAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $encoder = $this->container->get('security.password_encoder');

        $type=$em->getRepository('AppBundle:UserTypes')->findOneByType('ROLE_USER');
        $em->flush();

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = new User($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setRole($type);

        $em->persist($user);
        $em->flush($user);
        $nameUser=$user->getUsername();
        return new View("User $nameUser successfully created");
    }

}
