<?php


namespace App\Controller;

use App\Repository\ShowDateRepository;
use App\Repository\SpectacleRepository;
use App\Service\CalendarService;
use Mapado\RestClientSdk\SdkClient;
use Mapado\RestClientSdk\Tests\Units\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

/** Controls the Calendar Pages
 * Class CalendarController
 * @package App\Controller
 */
class CalendarController extends AbstractController
{

    private $calendarService;
    private $showDateRepository;
    private $spectacleRepository;

    public function __construct(
        CalendarService $calendarService,
        ShowDateRepository $showDateRepository,
        SpectacleRepository $spectacleRepository
    ) {
        $this->calendarService = $calendarService;
        $this->showDateRepository = $showDateRepository;
        $this->spectacleRepository = $spectacleRepository;
    }

    /**
     * Displays the calendar at first visit on Calendar Page.
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/calendar/",
     *      name="calendar_home",
     *     methods={"GET", "POST"})
     */
    public function calendarHome()
    {



        $today = new \DateTime();
        $todayString = $today->format("Y-m-d");

        return $this->render('Calendar/calendar.html.twig', [
            'today' => $today,
            // Array of spectacle object taking place on the selected day
            'spectaclesOfTheDay' => $this->calendarService->selectSpectaclesOfTheDay($todayString),
            // array of Strings applied in the twig date_modify filter to increment days in the calendar carousel
            'oneMoreDay' => $this->calendarService->addMoreDays()
        ]);
    }

    /**
     * Display the calendar with the selected date as input.
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @Route("/calendar/{day}", name="calendar_select_day")
     */
    public function calendarSelectedDay(Request $request)
    {

        //Retrieves the date passed in URI.
        $selectedDay = substr($request->getUri(), -10);

        //IF INPUT used // Date transmitted by the "rechercher par date" formular
        if ($request->request->get('picked_date')) {
            $selectedDateForm = $request->request->get('picked_date');

            return $this->render('Calendar/calendar.html.twig', [
                'today' => $selectedDateForm,
                // Array of spectacle object taking place on the selected day
                'spectaclesOfTheDay' => $this->calendarService->selectSpectaclesOfTheDay($selectedDateForm),
                // array of Strings applied in the twig date_modify filter to increment days in the calendar carousel
                'oneMoreDay' => $this->calendarService->addMoreDays()
            ]);
        } elseif (!empty($selectedDay)) {
            //If a date is passed in URI (selected on the carousel calendar)

            return $this->render('Calendar/calendar.html.twig', [
               'today' => $selectedDay,
               // Array of spectacle object taking place on the selected day
               'spectaclesOfTheDay' => $this->calendarService->selectSpectaclesOfTheDay($selectedDay),
               // array of Strings applied in the twig date_modify filter to increment days in the calendar carousel
               'oneMoreDay' => $this->calendarService->addMoreDays()
            ]);
        }
    }
}
