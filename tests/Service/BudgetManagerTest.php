<?php

namespace App\Tests\Service;

use App\Entity\Budget;
use App\Service\BudgetManager;
use PHPUnit\Framework\TestCase;

class BudgetManagerTest extends TestCase
{
    // =============================================
    // Test 1: Un budget valide passe la validation
    // =============================================
    public function testValidBudget(): void
    {
        $budget = new Budget();
        $budget->setMontantAlloue(10000);
        $budget->setMontantUtilise(3000);

        $manager = new BudgetManager();
        $this->assertTrue($manager->validate($budget));
    }

    // =============================================
    // Test 2: Le montant alloué doit être > 0
    // =============================================
    public function testBudgetWithZeroAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $budget = new Budget();
        $budget->setMontantAlloue(0);

        $manager = new BudgetManager();
        $manager->validate($budget);
    }

    // =============================================
    // Test 3: Le montant utilisé ne peut pas être négatif
    // =============================================
    public function testBudgetWithNegativeUsed(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $budget = new Budget();
        $budget->setMontantAlloue(5000);
        $budget->setMontantUtilise(-100);

        $manager = new BudgetManager();
        $manager->validate($budget);
    }

    // =============================================
    // Test 4: La date de modification doit être postérieure à la date d'allocation
    // =============================================
    public function testBudgetWithInvalidDates(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $budget = new Budget();
        $budget->setMontantAlloue(5000);
        $budget->setDateAllocation(new \DateTime('2026-03-10'));
        $budget->setDateModification(new \DateTime('2026-03-01'));

        $manager = new BudgetManager();
        $manager->validate($budget);
    }

    // =============================================
    // Test 5: Détection du dépassement de budget
    // =============================================
    public function testIsDepassement(): void
    {
        $budget = new Budget();
        $budget->setMontantAlloue(5000);
        $budget->setMontantUtilise(7000);

        $manager = new BudgetManager();
        $this->assertTrue($manager->isDepassement($budget));
    }

    // =============================================
    // Test 6: Pas de dépassement quand utilisé < alloué
    // =============================================
    public function testIsNotDepassement(): void
    {
        $budget = new Budget();
        $budget->setMontantAlloue(10000);
        $budget->setMontantUtilise(3000);

        $manager = new BudgetManager();
        $this->assertFalse($manager->isDepassement($budget));
    }

    // =============================================
    // Test 7: Calcul du montant restant
    // =============================================
    public function testMontantRestant(): void
    {
        $budget = new Budget();
        $budget->setMontantAlloue(10000);
        $budget->setMontantUtilise(3500);

        $manager = new BudgetManager();
        $this->assertEquals(6500, $manager->getMontantRestant($budget));
    }

    // =============================================
    // Test 8: Calcul du pourcentage d'utilisation
    // =============================================
    public function testPourcentageUtilisation(): void
    {
        $budget = new Budget();
        $budget->setMontantAlloue(10000);
        $budget->setMontantUtilise(2500);

        $manager = new BudgetManager();
        $this->assertEquals(25.0, $manager->getPourcentageUtilisation($budget));
    }

    // =============================================
    // Test 9: Pourcentage à 0 quand montant alloué = 0
    // =============================================
    public function testPourcentageUtilisationZeroBudget(): void
    {
        $budget = new Budget();
        $budget->setMontantAlloue(0);
        $budget->setMontantUtilise(0);

        $manager = new BudgetManager();
        $this->assertEquals(0, $manager->getPourcentageUtilisation($budget));
    }
}
