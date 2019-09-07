<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdminController extends FOSRestController
{
    private $userRepository;
    private $em;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->em = $entityManager;
    }

    /**
     * @Rest\Get("/api/admin/users")
     */
    public function getApiUsers(){
        $users = $this->userRepository->findAll();
        return $this->view($users);
    }

    /**
     * @Rest\Get("/api/admin/user/{id}")
     */
    public function getApiUser(User $user){
        return $this->view($user);
    }

    /**
     * @Rest\Post("/api/admin/user")
     * @ParamConverter ("user", converter="fos_rest.request_body")
     */
    public function postApiUser(User $user)
    {
        $this->em->persist($user);
        $this->em->flush();
        return $this->view($user);
    }


}
