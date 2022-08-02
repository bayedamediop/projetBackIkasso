<?php

namespace App\Controller;
use App\Entity\Articles;
use App\Entity\Clients;
use App\Entity\Reservations;
use App\Entity\Admin;
use App\Entity\AdminAgence;
use App\Entity\Agences;
use App\Entity\Profils;
use App\Entity\User;
use App\Entity\Utilisateur;
use App\Repository\ArticlesRepository;
use App\Repository\ReservationsRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Service\ValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
        //dd($userConnecte);
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
     *  path = "/api/admin/archive/{id}",
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

    // _______________________________Déjabonner user-------------------------

    /**
     * @Route(
     *  name = "dejabonnerUser",
     *  path = "/api/admin/dejabonner/{id}",
     *  methods = {"PUT"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminSystemeController::dejabonnerUser",
     *      "__api_ressource_class"=User::class,
     *      "__api_collection_operation_name"="dejabonner_user"
     * }
     * )
     */
    public function dejabonnerUser($id,UserRepository $userRepository,EntityManagerInterface $manager)
    {
        $user = $userRepository->find($id);

        $user->setAbonnement(false);
        $manager->flush();
        return new JsonResponse("dejabonnement Success !!!!!!!",200,[],true);

    }

    // _________________________Re abonnement user-------------------------

    /**
     * @Route(
     *  name = "reabonnerUser",
     *  path = "/api/admin/reabonner/{id}",
     *  methods = {"PUT"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminSystemeController::reabonnerUser",
     *      "__api_ressource_class"=User::class,
     *      "__api_collection_operation_name"="dejabonner_user"
     * }
     * )
     */
    public function reabonnerUser($id,UserRepository $userRepository,EntityManagerInterface $manager)
    {
        $user = $userRepository->find($id);

        $user->setAbonnement(true);
        $manager->flush();
        return new JsonResponse("reabonnement Success !!!!!!!",200,[],true);

    }

    // ______________ reservation d' un client dans un hotel  ______________________
    /**
     * @Route (
     *     name="reservation",
     *      path="/api/article/reserver",
     *      methods={"POST"},
     *     defaults={
     *           "__controller"="App\Controller\AdminAgenceController::reservation",
     *           "__api_ressource_class"=Articles::class,
     *           "__api_collection_operation_name"="reservation_d_un_client"
     *         }
     * )
     */
    public function reservation( Request $request,ReservationsRepository $repository,
                                 ArticlesRepository $articleRepository,UserRepository $userRepository): JsonResponse
    {
        $json = json_decode($request->getContent(), 'json');
        //dd($json);
        //dd($json['reservation'][0]['dateReservation']);
        $article=$articleRepository->find((int)$json['reservation'][0]['article']);
        $user_id=$article;
       // dd($user_id->getUser());
//        $dateFin=new \DateTime($json['reservation'][0]['dateFin']);
//        // dd($dateFin);
//        $dateDebut=new \DateTime($json['reservation'][0]['dateDebut']);
//        $reservation = $repository->findOneBy(['article' => $article], ['id' => 'desc']);

        $idUser = $userRepository->find((int)$article->getUser()->getId());
        $client = new Clients();
        $client->setNomComplet($json['nomClient'])

            ->setTelClient($json['telephoneClient'])
            ->setAdresseClient($json['adresseClient']);
        $em = $this->getDoctrine()->getManager();
        $em->persist($client);
        $reservation = new Reservations();
        //$reservation->setDateReservation($json['reservation'][0]['dateReservation'])
            $reservation ->setClient($client)
                ->setArticle($article)
                ->setUser($idUser);
        $em->persist($reservation);
        $em->flush();
        return new JsonResponse([
            'status' => 200,
            'message' => ('Votre reservation a ete bien enregistrer!!! merci '
            )
        ], 200);


    }
    // ___________________ modiffication d'un article ______________________

    /**
     *
     *   * @Route (
     *     name="putArticleId",
     *      path="/api/admin/article/{id}",
     *      methods={"PUT"},
     *     defaults={
     *           "__controller"="App\Controller\AdminAgenceController::putArticleId",
     *           "__api_ressource_class"=Articles::class,
     *           "__api_collection_operation_name"="put_ArticleId"
     *         }
     * )
     */
    public function putArticleId($id, UserService $service, Request $request,
                                 EntityManagerInterface $manager, SerializerInterface $serializer, ArticlesRepository $u)
    {

        $article = $service->getAttributes($request);
        // $userUpdate = $this->manager->getRepository(User::class)->find($id);
        $articleForm= $service->getAttributes($request, 'image3D');
        // dd($userForm);
        //$userUpdate = $service->PutUser($request, 'avatar');
        // dd($userUpdate);
        $articleForm = $manager->getRepository(Articles::class)->find($id);
        foreach($article as $key=>$valeur){
            $setter = 'set'.ucfirst(strtolower($key));
            if(method_exists(Articles::class, $setter)){

                $articleForm->$setter($valeur);
            }

        }
        // dd($user);
        $manager->flush();
        return new JsonResponse("success",200,[],true);
    }

    // _______________________________archiver un article-------------------------

    /**
     * @Route(
     *  name = "archiveArticle",
     *  path = "/api/article/{id}",
     *  methods = {"PUT"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminAgenceController::archiveArticle",
     *      "__api_ressource_class"=Articles::class,
     *      "__api_collection_operation_name"="archive_article"
     * }
     * )
     */
    public function archiveArticle($id,ArticlesRepository $articleRepository,EntityManagerInterface $manager)
    {
        $user = $articleRepository->find($id);
        $user->setArchivage(false);
        $manager->flush();
        return new JsonResponse("Article Archivé!!!!!!!",200,[],true);

    }
    // _______________________________ger reservation  d'un admin hotell ou agence-------------------------

    /**
     * @Route(
     *  name = "getReservationHotel",
     *  path = "/api/admin/article/{id}",
     *  methods = {"DELETE"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminAgenceController::getReservationHotel",
     *      "__api_ressource_class"=Articles::class,
     *      "__api_collection_operation_name"="get_Reservation_Hotel"
     * }
     * )
     */
    public function getReservationHotel($id,ArticlesRepository $articleRepository, TokenStorageInterface $token,EntityManagerInterface $manager)
    {

        $userConnecte = $token->getToken();
        // dd($userConnecte);
        //$userConnecte = $token->getToken()->getUser()->getId();
        $user = $articleRepository->find($id);
        $user->setArchivage(true);
        $manager->flush();
        return new JsonResponse("Article Archivé!!!!!!!",200,[],true);

    }

    // ___________________ valider une reservation_____________________________
    /**
     * @Route(
     *  name = "validerReservation",
     *  path = "/api/admin/validerReservation/{id}",
     *  methods = {"PUT"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminAgenceController::validerReservation",
     *      "__api_ressource_class"=Reservations::class,
     *      "__api_collection_operation_name"="valider_reservation"
     * }
     * )
     */
    public function validerReservation($id,ReservationsRepository $rerservationRepository,EntityManagerInterface $manager)
    {
        $reservation = $rerservationRepository->find($id);
        //dd($reservation);
        $reservation->setValiderRservation(true);
        //$manager->remove($reservation);
        $manager->flush();
        return new JsonResponse("La validation effectue avec success !!!!!!!",200,[],true);
    }

// ___________________annuler une reservation_____________________________
    /**
     * @Route(
     *  name = "annulerReservation",
     *  path = "/api/admin/annulerReservation/{id}",
     *  methods = {"PUT"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminAgenceController::annulerReservation",
     *      "__api_ressource_class"=Reservations::class,
     *      "__api_collection_operation_name"="annuler_reservation"
     * }
     * )
     */
    public function annulerReservation($id,ReservationsRepository $rerservationRepository,EntityManagerInterface $manager)
    {
        $reservation = $rerservationRepository->find($id);
        //dd($reservation);
        $reservation->setAnnulerRservation(true);
        $reservation->setValiderRservation(false);
        //$manager->remove($reservation);
        $manager->flush();
        return new JsonResponse("L' annulation effectue avec success !!!!!!!",200,[],true);
    }

//les commentaires d'un livrable partiel
    /**
     * @Route(
     *  name = "getReservationdunUser",
     *  path = "/api/admin/reservationArticle",
     *  methods = {"GET"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminAgenceController::getReservationdunUser",
     *      "__api_ressource_class"=Reservations::class,
     *      "__api_collection_operation_name"="get_reservationdunUser"
     * }
     * )
     */
    public function getReservationdunUser(ArticlesRepository $articlesRepository,ReservationsRepository $reservationsRepository)
    {
        $userConnecte = $this->getUser()->getId();
        $livrablepartiel = $reservationsRepository->ifUserInResevation($userConnecte);

        if ($livrablepartiel && $livrablepartiel[0]->getDateValidation() != null ) {
            // dd($livrablepartiel[0]->getDateValidation() == null);
//            for ($i=0; $i < count($livrablepartiel) ; $i++) {
//                $articlr= $reservationsRepository->ifArticleInResevation($livrablepartiel[$i]->getId());
            return $this->json($livrablepartiel,200,[],['groups'=>"getReservationdunUser"]);
//            }
            //dd($livrablepartiel[0]->getId());

            //$articlr = $articlesRepository->ifUserInArticle($userConnecte);

            //sreturn $this->json($livrablepartiel,200,[],['groups'=>"getReservationdunUser"]);
        }
        return new JsonResponse("ececc!!!!!!!",400,[],false);
    }
    /**
     * @Route(
     *  name = "reservationdunUser",
     *  path = "/api/reservation/valider",
     *  methods = {"GET"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminSystemeController::reservationdunUser",
     *      "__api_ressource_class"=Reservations::class,
     *      "__api_collection_operation_name"="get_reservationdunUser"
     * }
     * )
     */
    public function reservationdunUser(ReservationsRepository $reservationsRepository)
    {
        $userConnecte = $this->getUser()->getId();
        //dd($userConnecte);
        $reservationsRepository = $reservationsRepository->ifUserInReservation($userConnecte);
       // dd($reservationsRepository);
        for ($i=0; $i < count($reservationsRepository) ; $i++) {
            if ($reservationsRepository[$i]->getValiderRservation() == 1 ) {
                return $this->json($reservationsRepository[$i],200,[],['groups'=>"getReservationdunUser"]);
                }
        }
        //if ($reservationsRepository ) {

        //}
        //return $this->json($reservationsRepository,200,[],['groups'=>"getReservationdunUser"]);
       // if ($livrablepartiel ) {
            // dd($livrablepartiel[0]->getDateValidation() == null);
//            for ($i=0; $i < count($livrablepartiel) ; $i++) {
//                $articlr= $reservationsRepository->ifArticleInResevation($livrablepartiel[$i]->getId());
           // return $this->json($livrablepartiel,200,[],['groups'=>"getReservationdunUser"]);
//            }
            //dd($livrablepartiel[0]->getId());

            //$articlr = $articlesRepository->ifUserInArticle($userConnecte);

            //sreturn $this->json($livrablepartiel,200,[],['groups'=>"getReservationdunUser"]);
      //  }
       return new JsonResponse("il y'a pas des reservation a valider !!!!!!!!!!!!!!",400,[],false);
    }
    /**
     * @Route(
     *  name = "reservationdunannuler",
     *  path = "/api/reservation/annuler",
     *  methods = {"GET"},
     *  defaults  = {
     *      "__controller"="App\Controller\AdminSystemeController::reservationdunannuler",
     *      "__api_ressource_class"=Reservations::class,
     *      "__api_collection_operation_name"="get_reservationdunUser"
     * }
     * )
     */
    public function reservationdunannuler(ReservationsRepository $reservationsRepository)
    {
        $userConnecte = $this->getUser()->getId();
        //dd($userConnecte);
        $reservationsRepository = $reservationsRepository->ifUserInReservation($userConnecte);
        // dd($reservationsRepository);
        for ($i=0; $i < count($reservationsRepository) ; $i++) {
            if ($reservationsRepository[$i]->getAnnulerRservation() == 1) {
                return $this->json($reservationsRepository[$i],200,[],['groups'=>"getReservationdunUser"]);
            }
        }
        return new JsonResponse("il y'a pas des reservation annuler!!!!!!!",400,[],false);
    }


}
