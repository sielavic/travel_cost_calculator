<?php
namespace App\Controller;

use DateTime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TravelCostController extends AbstractController
{
    #[Route('/calculate-cost', methods: ['POST'])]
    public function calculateCost(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $earlyBookingDiscount = 0;
        $baseCost = $data['base_cost']; // Общая стоимость
        $participant = ($data['participant']);

        $birthDate = new \DateTime($participant);
        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;


        $travelDate = new \DateTime($data['travel_date']); // Дата начала путешествия

        $childDiscountedCost = $this->calculateChildDiscount($baseCost, $age);
        if (isset($data['payment_date'])) {
            $paymentDate = new \DateTime($data['payment_date']); // Дата оплаты
            $earlyBookingDiscount = $this->calculateEarlyBookingDiscount($childDiscountedCost, $paymentDate, $travelDate);
        }
        $finalCost = $childDiscountedCost - $earlyBookingDiscount;

        return new JsonResponse(['final_cost' => max($finalCost, 0)], 200);
    }

    private function calculateChildDiscount($baseCost, $age)
    {
        $discountedCost = $baseCost;

        if ($age > 3 && $age < 6) {
            $discountedCost -= 0.8 * $baseCost; // 80% скидка
        } elseif ($age < 12) {
            $discountedCost -= min(0.3 * $baseCost, 4500); // 30% на 4500 ₽
        } elseif($age > 12 && $age < 18) {
            $discountedCost -= 0.1 * $baseCost; // 10% скидка
        }

        return $discountedCost;
    }

    private function calculateEarlyBookingDiscount($childDiscountedCost, $paymentDate, $travelDate)
    {
        $discount = 0;
        $maxDiscount = 1500;

        // Условия для путешествий с 1 апреля по 30 сентября следующего года
        if ($travelDate >= new DateTime($travelDate->format('Y') . '-04-01') && $travelDate <= new DateTime($travelDate->format('Y') . '-09-30')) {
            if ($paymentDate >= new DateTime($paymentDate->format('Y') . '-11-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-12-01')) {
                $discount = min(0.07 * $childDiscountedCost, $maxDiscount);
            } elseif ($paymentDate >= new DateTime($paymentDate->format('Y') . '-12-01') && $paymentDate < new DateTime($paymentDate->format('Y') + 1 . '-01-01')) {
                $discount = min(0.05 * $childDiscountedCost, $maxDiscount);
            } elseif ($paymentDate >= new DateTime($paymentDate->format('Y') . '-01-01') && $paymentDate < new DateTime($paymentDate->format('Y')  . '-02-01')) {
                $discount = min(0.03 * $childDiscountedCost, $maxDiscount);
            }
        }

        // Условия для путешествий с 1 октября текущего года по 14 января следующего года
        elseif ($travelDate >= new DateTime($travelDate->format('Y') . '-10-01') && $travelDate <= new DateTime($travelDate->format('Y') + 1 . '-01-14')) {
            if ($paymentDate >= new DateTime($paymentDate->format('Y') . '-03-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-04-01')) {
                $discount = min(0.07 * $childDiscountedCost, $maxDiscount);
            } elseif ($paymentDate >= new DateTime($paymentDate->format('Y') . '-04-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-05-01')) {
                $discount = min(0.05 * $childDiscountedCost, $maxDiscount);
            } elseif ($paymentDate >= new DateTime($paymentDate->format('Y') . '-05-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-06-01')) {
                $discount = min(0.03 * $childDiscountedCost, $maxDiscount);
            }
        }

        // Условия для путешествий с 15 января следующего года и далее
        elseif ($travelDate > new DateTime($travelDate->format('Y') . '-01-14')) {
            if ($paymentDate >= new DateTime($paymentDate->format('Y') . '-08-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-09-01')) {
                $discount = min(0.07 * $childDiscountedCost, $maxDiscount);
            } elseif ($paymentDate >= new DateTime($paymentDate->format('Y') . '-09-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-10-01')) {
                $discount = min(0.05 * $childDiscountedCost, $maxDiscount);
            } elseif ($paymentDate >= new DateTime($paymentDate->format('Y') . '-10-01') && $paymentDate < new DateTime($paymentDate->format('Y') . '-11-01')) {
                $discount = min(0.03 * $childDiscountedCost, $maxDiscount);
            }
        }

        return $discount;
    }
}