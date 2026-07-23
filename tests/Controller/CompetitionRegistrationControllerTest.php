<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\CompetitionRegistrationController;
use App\Entity\Competition;
use App\Entity\CompetitionRegistration;
use App\Entity\Ranch;
use App\Entity\StatusCompetition;
use App\Form\CompetitionRegistrationType;
use App\Form\CompetitionType;
use App\Repository\RanchRepository;
use App\Repository\StatusCompetitionRepository;
use App\Service\CompetitionRegistrationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

final class CompetitionRegistrationControllerTest extends TestCase
{
    public function testRedirectsWhenNoRanchIsAssociatedWithUser(): void
    {
        $user = $this->createMock(UserInterface::class);
        $competition = $this->createMock(Competition::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $ranchRepository = $this->createMock(
            RanchRepository::class,
        );

        $ranchRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['owner' => $user])
            ->willReturn(null);

        $registrationService = $this->createMock(
            CompetitionRegistrationService::class,
        );

        $registrationService
            ->expects(self::never())
            ->method('registerCouple');

        $statusRepository = $this->createMock(
            StatusCompetitionRepository::class,
        );

        $controller = new TestableCompetitionRegistrationController();
        $controller->currentUser = $user;

        $response = $controller->register(
            $competition,
            Request::create('/competition/1/register'),
            $registrationService,
            $ranchRepository,
            $statusRepository,
            $entityManager
        );

        self::assertInstanceOf(
            RedirectResponse::class,
            $response,
        );

        self::assertSame(
            '/app_competition_index',
            $response->headers->get('Location'),
        );

        self::assertSame(
            [
                [
                    'danger',
                    'Aucune écurie n\'est associée à votre compte.',
                ],
            ],
            $controller->flashes,
        );
    }

    public function testRegistersValidCoupleWithProposedStatus(): void
    {
        $user = $this->createMock(UserInterface::class);
        $ranch = $this->createMock(Ranch::class);
        $otherRanch = $this->createMock(Ranch::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);


        $competition = $this->createMock(Competition::class);

        $competition
            ->method('getLocation')
            ->willReturn($otherRanch);

        $competition
            ->method('getId')
            ->willReturn(42);

        $ranchRepository = $this->createMock(
            RanchRepository::class,
        );

        $ranchRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['owner' => $user])
            ->willReturn($ranch);

        $registrationForm = $this->createMock(
            FormInterface::class,
        );

        $request = Request::create(
            '/competition/42/register',
            Request::METHOD_POST,
            [
                'competition_registration' => [],
            ],
        );

        $registrationForm
            ->expects(self::once())
            ->method('handleRequest')
            ->with(self::identicalTo($request));

        $registrationForm
            ->expects(self::once())
            ->method('isSubmitted')
            ->willReturn(true);

        $registrationForm
            ->expects(self::once())
            ->method('isValid')
            ->willReturn(true);

        $status = $this->createMock(
            StatusCompetition::class,
        );

        $statusRepository = $this->createMock(
            StatusCompetitionRepository::class,
        );

        $statusRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['mnemonique' => 'PROPOSEE'])
            ->willReturn($status);

        $registrationService = $this->createMock(
            CompetitionRegistrationService::class,
        );

        $registrationService
            ->expects(self::once())
            ->method('registerCouple')
            ->with(
                self::callback(
                    static function (
                        CompetitionRegistration $registration,
                    ) use ($status): bool {
                        self::assertSame(
                            $status,
                            $registration->getStatusRegistration(),
                        );

                        return true;
                    },
                ),
                self::identicalTo($competition),
            );

        $controller = new TestableCompetitionRegistrationController();
        $controller->currentUser = $user;
        $controller->forms = [$registrationForm];

        $response = $controller->register(
            $competition,
            $request,
            $registrationService,
            $ranchRepository,
            $statusRepository,
            $entityManager
        );

        self::assertInstanceOf(
            RedirectResponse::class,
            $response,
        );

        self::assertSame(
            '/app_competition_register?id=42',
            $response->headers->get('Location'),
        );

        self::assertSame(
            CompetitionRegistrationType::class,
            $controller->createdForms[0]['type'],
        );

        self::assertSame(
            [
                [
                    'success',
                    'Le couple a été proposé avec succès.',
                ],
            ],
            $controller->flashes,
        );
    }

    public function testThrowsExceptionWhenProposedStatusDoesNotExist(): void
    {
        $user = $this->createMock(UserInterface::class);
        $ranch = $this->createMock(Ranch::class);
        $otherRanch = $this->createMock(Ranch::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);


        $competition = $this->createMock(Competition::class);

        $competition
            ->method('getLocation')
            ->willReturn($otherRanch);

        $ranchRepository = $this->createMock(
            RanchRepository::class,
        );

        $ranchRepository
            ->method('findOneBy')
            ->willReturn($ranch);

        $registrationForm = $this->createMock(
            FormInterface::class,
        );

        $registrationForm
            ->method('isSubmitted')
            ->willReturn(true);

        $registrationForm
            ->method('isValid')
            ->willReturn(true);

        $statusRepository = $this->createMock(
            StatusCompetitionRepository::class,
        );

        $statusRepository
            ->expects(self::once())
            ->method('findOneBy')
            ->with(['mnemonique' => 'PROPOSEE'])
            ->willReturn(null);

        $registrationService = $this->createMock(
            CompetitionRegistrationService::class,
        );

        $registrationService
            ->expects(self::never())
            ->method('registerCouple');

        $controller = new TestableCompetitionRegistrationController();
        $controller->currentUser = $user;
        $controller->forms = [$registrationForm];

        $request = Request::create(
            '/competition/42/register',
            Request::METHOD_POST,
            [
                'competition_registration' => [],
            ],
        );

        $this->expectException(\Exception::class);

        $this->expectExceptionMessage(
            "Le statut avec le mnémonique 'PROPOSEE' n'existe pas en base de données.",
        );

        $controller->register(
            $competition,
            $request,
            $registrationService,
            $ranchRepository,
            $statusRepository,
            $entityManager
        );
    }

    public function testDisplaysCompetitionFormForHostingRanch(): void
    {
        $user = $this->createMock(UserInterface::class);
        $ranch = $this->createMock(Ranch::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);


        $competition = $this->createMock(Competition::class);

        $competition
            ->method('getLocation')
            ->willReturn($ranch);

        $ranchRepository = $this->createMock(
            RanchRepository::class,
        );

        $ranchRepository
            ->method('findOneBy')
            ->willReturn($ranch);

        $competitionForm = $this->createMock(
            FormInterface::class,
        );

        $competitionFormView = new FormView();

        $competitionForm
            ->expects(self::once())
            ->method('createView')
            ->willReturn($competitionFormView);

        $registrationForm = $this->createMock(
            FormInterface::class,
        );

        $registrationForm
            ->expects(self::once())
            ->method('createView')
            ->willReturn(new FormView());

        $registrationService = $this->createMock(
            CompetitionRegistrationService::class,
        );

        $statusRepository = $this->createMock(
            StatusCompetitionRepository::class,
        );

        $controller = new TestableCompetitionRegistrationController();
        $controller->currentUser = $user;
        $controller->forms = [
            $competitionForm,
            $registrationForm,
        ];

        $response = $controller->register(
            $competition,
            Request::create('/competition/42/register'),
            $registrationService,
            $ranchRepository,
            $statusRepository,
            $entityManager
        );

        self::assertSame(
            Response::HTTP_OK,
            $response->getStatusCode(),
        );

        self::assertSame(
            [
                CompetitionType::class,
                CompetitionRegistrationType::class,
            ],
            array_column(
                $controller->createdForms,
                'type',
            ),
        );

        self::assertTrue(
            $controller->renderParameters['canEditCompetition'],
        );

        self::assertSame(
            $competitionFormView,
            $controller->renderParameters['competitionForm'],
        );
    }
}

/**
 * Contrôleur utilisé uniquement pour isoler les dépendances fournies
 * normalement par AbstractController.
 */
final class TestableCompetitionRegistrationController extends
    CompetitionRegistrationController
{
    public ?UserInterface $currentUser = null;

    public bool $admin = false;

    /**
     * @var list<FormInterface>
     */
    public array $forms = [];

    /**
     * @var list<array{type: string, data: mixed, options: array}>
     */
    public array $createdForms = [];

    /**
     * @var list<array{0: string, 1: mixed}>
     */
    public array $flashes = [];

    /**
     * @var array<string, mixed>
     */
    public array $renderParameters = [];

    protected function getUser(): ?UserInterface
    {
        return $this->currentUser;
    }

    protected function isGranted(
        mixed $attribute,
        mixed $subject = null,
    ): bool {
        if ($attribute === 'ROLE_ADMIN') {
            return $this->admin;
        }

        return false;
    }

    protected function createForm(
        string $type,
        mixed $data = null,
        array $options = [],
    ): FormInterface {
        $this->createdForms[] = [
            'type' => $type,
            'data' => $data,
            'options' => $options,
        ];

        $form = array_shift($this->forms);

        if (!$form instanceof FormInterface) {
            throw new \LogicException(
                sprintf(
                    'Aucun formulaire simulé disponible pour "%s".',
                    $type,
                ),
            );
        }

        return $form;
    }

    protected function addFlash(
        string $type,
        mixed $message,
    ): void {
        $this->flashes[] = [$type, $message];
    }

    protected function redirectToRoute(
        string $route,
        array $parameters = [],
        int $status = Response::HTTP_FOUND,
    ): RedirectResponse {
        $query = http_build_query($parameters);

        $url = '/' . $route;

        if ($query !== '') {
            $url .= '?' . $query;
        }

        return new RedirectResponse($url, $status);
    }

    protected function render(
        string $view,
        array $parameters = [],
        ?Response $response = null,
    ): Response {
        $this->renderParameters = $parameters;

        return $response ?? new Response('Rendered template');
    }
}