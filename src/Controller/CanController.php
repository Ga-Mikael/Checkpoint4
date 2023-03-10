<?php

namespace App\Controller;

use App\Entity\Can;
use App\Form\CanType;
use App\Service\BunkerManager;
use App\Repository\CanRepository;
use App\Repository\BunkerRepository;
use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[IsGranted('ROLE_USER')]
#[Route('/can')]
class CanController extends AbstractController
{

    public function __construct(private BunkerManager $bunkerManager)
    {
    }

    #[Route('/', name: 'app_can_index', methods: ['GET'])]
    public function index(BunkerManager $bunkerManager, CanRepository $canRepository): Response
    {
        /**  @var \App\Entity\User */
        $user = $this->getUser();
        $bunker = $user->getBunker();

        $cans = $canRepository->findBy(['bunkerStock' => $bunker], ['expirationDate' => 'ASC']);

         /** @var DateTime */
       /*  $now = new DateTime('now');
        $peremptionDate = $canRepository->findBy(['ExpirationDate' => $expirationDate]);
        $DayBeforePeremption = date_diff($now, $peremptionDate);

        return $DayBeforePeremption->format('‰a jours restant'); */ 

        return $this->render('can/index.html.twig', [
            'bunker' => $bunker,
            'canStock' => $bunkerManager->getAllCan($bunker),
            'cans' => $cans,
            /* 'dayBeforePeremption' => $bunkerManager->getDayBeforePeremption(), */
        ]);
    }

    #[Route('/new', name: 'app_can_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CanRepository $canRepository): Response
    {
        $can = new Can();
        $form = $this->createForm(CanType::class, $can);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $canRepository->save($can, true);

            return $this->redirectToRoute('app_can_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('can/new.html.twig', [
            'can' => $can,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_can_show', methods: ['GET'])]
    public function show(Can $can): Response
    {
        return $this->render('can/show.html.twig', [
            'can' => $can,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_can_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Can $can, CanRepository $canRepository): Response
    {
        $form = $this->createForm(CanType::class, $can);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $canRepository->save($can, true);

            return $this->redirectToRoute('app_can_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('can/edit.html.twig', [
            'can' => $can,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_can_delete', methods: ['POST'])]
    public function delete(Request $request, Can $can, CanRepository $canRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$can->getId(), $request->request->get('_token'))) {
            $canRepository->remove($can, true);
        }

        return $this->redirectToRoute('app_can_index', [], Response::HTTP_SEE_OTHER);
    }
}
