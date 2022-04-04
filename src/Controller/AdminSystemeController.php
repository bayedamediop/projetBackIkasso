<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\AdminAgence;
use App\Entity\Agences;
use App\Entity\Profils;
use App\Entity\User;
use App\Entity\Utilisateur;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class AdminSystemeController extends AbstractController
{
    /**
     * @Route("/admin/systeme", name="app_admin_systeme")
     */
    public function index(): Response
    {
        return $this->render('admin_systeme/index.html.twig', [
            'controller_name' => 'AdminSystemeController',
        ]);
    }
    private $encoder;
    private $manager;
    public function  __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $this->encoder=$encoder;
        $this->manager=$manager;
    }

    /**
     * @Route(
     *  name="addUser",
     *  path="/api/admin/users",
     *  methods={"POST"},
     *  defaults={
     *      "_controller"="\app\Controller\User::addUser",
     *      "_api_collection_operation_name"="add_user"
     *  }
     * )
     */
    public function addUser(SerializerInterface $serializer,Request $request,ValidatorService $validate)
    {
        //$userConnecte = $token->getToken()->getUser();
        $userConnecte = $this->getUser();
        dd($userConnecte);
        $user = $request->request->all();
        //dd($user);
        $img = $request->files->get("avatar");

        if($img){
            $img = fopen($img->getRealPath(), "rb");
        }
        //dd($user['nomAgence']);
        $manager=$this->getDoctrine()->getManager();
        $profil= $manager->getRepository(Profils::class)->findOneBy(['libelle' => $user['profils']]);
        if($user['profils'] === "adminAgence"){
            $userObject = $serializer->denormalize($user, AdminAgence::class);
            $agence = $serializer->denormalize($user, Agences::class);

            $userObject->setAvatar($img);
            // dd($newagence);
            $userObject->setProfil($profil);
            $agence->setUser($userObject);

            //$userObject->setProfil($this->manager->getRepository(Profil::class)->findOneBy(['libelle' => $user['profils']]));
            $userObject ->setPassword ($this->encoder->encodePassword ($userObject, $user['password']));
            // $validate->validate($userObject);
            // dd($userObject);
            $this->manager->persist($userObject);
            $this->manager->persist($agence);
        }

        if($user['profils'] === "admin"){
        $userObject = $serializer->denormalize($user, Admin::class);
        $userObject->setAvatar($img);
        // dd($userObject);
        $userObject->setProfil($profil);

        //  $userObject->setProfil($this->manager->getRepository(Profil::class)->findOneBy(['libelle' => $user['profils']]));
        $userObject ->setPassword ($this->encoder->encodePassword ($userObject, $user['password']));
        $validate->validate($userObject);
        // dd($userObject);
        $this->manager->persist($userObject);
    }
        if($user['profils'] === "utilisateur"){
            $userObject = $serializer->denormalize($user, Utilisateur::class);
            $userObject->setAvatar($img);
            // dd($userObject);
            $userObject->setProfil($profil);

            //  $userObject->setProfil($this->manager->getRepository(Profil::class)->findOneBy(['libelle' => $user['profils']]));
            $userObject ->setPassword ($this->encoder->encodePassword ($userObject, $user['password']));
            $validate->validate($userObject);
            // dd($userObject);
            $this->manager->persist($userObject);

        }
        //dd($img);
        //dd($user);
        $userObject->setAvatar($img);
        // dd($userObject);
        $userObject->setProfil($profil);

        //  $userObject->setProfil($this->manager->getRepository(Profil::class)->findOneBy(['libelle' => $user['profils']]));
        $userObject ->setPassword ($this->encoder->encodePassword ($userObject, $user['password']));
        $validate->validate($userObject);
        // dd($userObject);
        $this->manager->persist($userObject);
        $this->manager->flush();
        //return $this->json($userObject,Response::HTTP_OK);

        return $this->json("success",Response::HTTP_OK);


    }

    /**
     * @Route(
     *  name="putUser",
     *  path="/api/admin/user/{id}",
     *  methods={"PUT"},
     *  defaults={
     *      "__controller"="App\Controller\AdminSystemeController::putUser",
     *      "_api_collection_operation_name"="put_user",
     *      "api_resource_class"=User::class
     *  }
     * )
     * @param $id
     * @param UserService $service
     * @param Request $request
     * @return JsonResponse
     */
    public function putUser($id, UserService $service,Request $request)
    {
        $user = $service->getAttributes($request);
        $userUpdate = $this->manager->getRepository(User::class)->find($id);
        foreach($user as $key=>$valeur){
            $setter = 'set'.ucfirst(strtolower($key));
            if(method_exists(User::class, $setter)){
                if($key === "profil"){
                    $userUpdate->$setter($this->manager->getRepository(Profils::class)->findOneBy(['libelle' => $valeur]));
                }
                elseif($key === "password"){
                    $userUpdate->$setter($this->encoder->encodePassword ($userUpdate, $valeur));
                }else{
                    $userUpdate->$setter($valeur);
                }


            }
        }
        $this->manager->flush();
        return $this->json("success",Response::HTTP_OK);

    }
    // _______________________________archiver un user-------------------------

    /**
     * @Route(
     *  name = "archiveUser",
     *  path = "/api/admin/user/{id}",
     *  methods = {"PUT"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminSystemeController::archiveUser",
     *      "__api_ressource_class"=User::class,
     *      "__api_collection_operation_name"="archive_user"
     * }
     * )
     */
    public function archiveUser($id,UserRepository $userRepository,EntityManagerInterface $manager)
    {
        $user = $userRepository->find($id);
        $user->setArchivage(false);
        $manager->flush();
        return new JsonResponse("Archivé Success !!!!!!!",200,[],true);

    }


}
