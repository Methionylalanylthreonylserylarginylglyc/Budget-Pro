<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function getApiUsers()
    {
        $users = $this->userRepository->findAll();
        return $this->view($users);
    }

    /**
     * @Rest\Get("/api/admin/user/{id}")
     */
    public function getApiUser(User $user)
    {
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

    /**
     * @Rest\Patch("/api/admin/user/{email}")
     */
    public function patchApiUser(User $user, Request $request, ValidatorInterface $validator)
    {
        // Normalement, le tableau d'attributs ne se ferait pas ici. On opterait plutôt
        // pour la création d'un Provider qui fournirait à l'application le tableau des attributs
        $attributes = [
            'firstname' => 'setFirstname',
            'lastname' => 'setLastname',
            'email' => 'setEmail'
        ];
        foreach ($attributes as $attributeName => $setterName) {
            if (is_null($request->get($attributeName))) {
                continue;
            }
            $user->$setterName($request->request->get($attributeName));
        }

        $validationErrors = $validator->validate($user);
        if ($validationErrors->count() > 0){
            /** @var ConstraintViolation $constraintViolation */
            foreach ($validationErrors as $constraintViolation){
                $message = $constraintViolation->getMessage();
                $propertyPath = $constraintViolation->getPropertyPath();
                $errors[] = ['message' => $message, 'propertyPath' => $propertyPath];
            }
        }

        if (!empty($errors)){
            //throw new BadRequestHttpException(json_encode($errors));
        }
        $this->em->flush();
        return $this->view($user);
    }

    /**
     * @Rest\Delete("/api/admin/user/{id}")
     */
    public function deleteApiUser(User $user)
    {
        $cards = $user->getCards();
        foreach ($cards as $card)
        {
            $this->em->remove($card);
        }

        $this->em->remove($user);
        $this->em->flush();
        return new Response(null, 204);
    }


}
