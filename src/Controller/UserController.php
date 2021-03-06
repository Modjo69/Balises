<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MdpType;
use App\Form\UserType;
use App\Entity\Theater;
use App\Form\RegistrationType;
use App\Service\TriPageService;
use App\Repository\UserRepository;
use App\Repository\ShowDateRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     *Create Index user
     * @Route("/index", name="user_index", methods={"GET"})
     * @Route("/index/{page_cours}/{ligne_page}/{champ}/{sens}",
     * name="user_index", methods={"GET"},
     * defaults={"champ":"" , "sens":"", "page_cours":1, "ligne_page":5})
     * @IsGranted("ROLE_ADMIN")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(
        string $champ,
        string $sens,
        UserRepository $userRepository,
        TriPageService $triPage,
        $page_cours,
        $ligne_page
    ): Response {
        
        $users=$triPage->paginationTri($page_cours, $ligne_page, $champ, $sens);

        $usersAll = $userRepository->findAll();

        //Nombre de ligne total
        $ligne_totale=count($usersAll);

        //calcul du nombre de pages totales
        $page_total=ceil($ligne_totale/$ligne_page);

        /*AFFICHAGE BANDEAU PAGINATION PAR GROUPE DE 5 MAX */
        $indice_page=$triPage->indicePage($page_cours, $ligne_page);
                    
         /****************************VUE*******************************/
        return $this->render('user/index.html.twig', [
            'users'         => $users,
            'page_cours'    => $page_cours,
            'ligne_page'    => $ligne_page,
            'indice_page'   => $indice_page,
            'page_total'    => $page_total

        ]);
    }
    /**
     * Create New user
     * @Route("/new", name="user_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param ObjectManager $manager
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function new(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder): Response
    {
        $user = new User();
        $theater = new Theater();
        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Haschage Password
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);

            // Set ROLE_THEATER for every new users
            $user->setRoles(['ROLE_THEATER']);
            $user->setTheater($theater);

            $theater->setName($request->request->get('registration')['theater']['name']);
            $theater->setEmail($user->getEmail());
            $theater->setUser($user);

            $manager->persist($user);
            $manager->persist($theater);
            $manager->flush();

             return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'theater' => $theater
        ]);
    }

    /**
     * @Route("/{id}",
     * requirements={"id"="\d+"},
     * name="user_show", methods={"GET"})
     * @IsGranted("ROLE_THEATER")
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }


    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_THEATER")
     * @param Request $request
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('user_index', [
                'id' => $user->getId(),
            ]);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit_mdp", name="theater_edit_mdp", methods={"GET","POST"})
     * @param Request $request
     * @param Theater $theater
     * @param User $user
     * @param UserPasswordEncoderInterface $encoder
     * @return RedirectResponse|Response
     */
    public function editMdp(
        Request $request,
        Theater $theater,
        User $user,
        UserPasswordEncoderInterface $encoder
    ) {

        // Empêche un théâtre d'acceder au compte d'un autre théâtre
        $user1 = $this->getUser()->getId();
        $userId = $user->getId();
        if ($user1 != $userId && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Acces denied ! Vous n'avez pas les droits");
        }

        $form = $this->createForm(MdpType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Mdp changé !');

            return $this->redirectToRoute('theater_edit', [
                'id' => $theater->getId() -1
            ]);
//        } else {
//            $this->addFlash('danger', 'Les mot de passe ne correspondent pas');
//            return $this->redirectToRoute('theater_edit_mdp', [
//                'id' => $user->getId()
//            ]);
//        }
        }
        return $this->render('theater/editMdp.html.twig', [
            'form' => $form->createView(),
            'theater' => $theater,
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param User $user
     * @return Response
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/chart", name="user_chart", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     *
     */
    public function showChart(ShowDateRepository $repo, SerializerInterface $serializer)
    {
        $mois=$repo->findDateMois();
        $years=$repo->findDateYear();
        $nbSpectacleAn=$repo->findSpectacleYear();

       
        $json_mois = $serializer->serialize(
            $mois,
            'json',
            ['groups' => 'group1']
        );

        $json_years = $serializer->serialize(
            $years,
            'json',
            ['groups' => 'group1']
        );

        $json_nbSpectacleAn = $serializer->serialize(
            $nbSpectacleAn,
            'json',
            ['groups' => 'group1']
        );
              
        return $this->render(
            'user/chart.html.twig',
            [
            'nbSpectacleMois' =>$json_mois,
            'annee' =>$json_years,
            'nbSpectacleAn'=> $json_nbSpectacleAn
            ]
        );
    }
}
