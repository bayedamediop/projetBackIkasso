<?php

namespace App\Controller;

use App\Entity\AdminAgence;
use App\Entity\Articles;
use App\Repository\ArticlesRepository;
use App\Repository\UserRepository;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class AdminAgenceController extends AbstractController
{
    /**
     * @Route("/admin/agence", name="app_admin_agence")
     */
    public function index(): Response
    {
        return $this->render('admin_agence/index.html.twig', [
            'controller_name' => 'AdminAgenceController',
        ]);
    }
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

// ______________ publier un article  ______________________
    /**
     * @Route (
     *     name="publication",
     *      path="/api/admin/article",
     *      methods={"POST"},
     *     defaults={
     *           "__controller"="App\Controller\AdminAgenceController::publication",
     *           "__api_ressource_class"=Articles::class,
     *           "__api_collection_operation_name"="publication_d_un_article"
     *         }
     * )
     */
    public function publication(SerializerInterface $serializer, TokenStorageInterface $token,Request $request,UserRepository $repository)
    {

        $userConnecte = $this->getUser()->getId();
         //dd($userConnecte);
        $ucecreer = $repository->find((int)$userConnecte);
        //dd($ucecreer);
        $article = $request->request->all();
        //dd($user);
        $image = $request->files->get("image");

        if($image){
            $image = fopen($image->getRealPath(), "rb");
        }else{
            return new JsonResponse("veuillez mettre une images d article",Response::HTTP_BAD_REQUEST,[],true);

        }
        $image3D= $request->files->get("image_3_d");

        if($image3D){
            $image3D = fopen($image3D->getRealPath(), "rb");
        }else{
            return new JsonResponse("veuillez mettre une image 3D",Response::HTTP_BAD_REQUEST,[],true);

        }
        $video= $request->files->get("video");

        if($video){
            $video = fopen($video->getRealPath(), "rb");
        }else{
            return new JsonResponse("veuillez mettre le video",Response::HTTP_BAD_REQUEST,[],true);

        }
        $article = $request->request->all() ;
        //dd($article);
        $image_article = $request->files->get("image_article");
        //specify entity
        //dd($photo);
//        if(!$image_article)
//        {
//            return new JsonResponse("veuillez mettre une images d article",Response::HTTP_BAD_REQUEST,[],true);
//        }

        $picture3_d = $request->files->get("picture3_d");
        //specify entity
        //dd($photo);
//        if(!$picture3_d)
//        {
//            return new JsonResponse("veuillez mettre une images de 3 D",Response::HTTP_BAD_REQUEST,[],true);
//        }
        $Object = $serializer->denormalize($article, Articles::class);
        $Object->setImage($image);
        $Object->setImage3D($image3D);
        $Object->setVideo($video);
        $Object->setDateCreation(new \DateTime());
        // dd($newagence);
        $Object->setUser($ucecreer);
        //$base64 = base64_decode($imagedata);
        //$photoBlob = fopen($image_article->getRealPath(),"rb");
        //$photoBlob3d = fopen($picture3_d->getRealPath(),"rb");

        //$articles = $serializer->denormalize($article, "App\Entity\Article");

//        $articles = new  Articles();
//        $articles->setDescription($article['description']);
//        $articles->setImage($photoBlob);
//        $articles->setImage3D($photoBlob3d);
//        $articles ->setPrix($article['prix']);
//        $articles ->setAdresseArticle($article['adresse_article']);
//        $articles->setDateCreation(new \DateTime());
//        $articles->setUser($ucecreer);
        // dd($articles);
        $em = $this->getDoctrine()->getManager();
        $em->persist($Object);
        $em->flush();

        return $this->json("success",201);

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

}
