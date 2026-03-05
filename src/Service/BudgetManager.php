<?php

namespace App\Service;

use App\Entity\Budget;

class BudgetManager
{
    /**
     * Validate a Budget entity against business rules.
     *
     * Rules:
     * 1. Le montant alloué doit être supérieur à zéro
     * 2. Le montant utilisé ne peut pas être négatif
     * 3. La date de fin (dateModification) doit être postérieure à la date de début (dateAllocation)
     */
    public function validate(Budget $budget): bool
    {
        if (empty($budget->getMontantAlloue()) || $budget->getMontantAlloue() <= 0) {
            throw new \InvalidArgumentException('Le montant alloué doit être supérieur à zéro');
        }

        if ($budget->getMontantUtilise() < 0) {
            throw new \InvalidArgumentException('Le montant utilisé ne peut pas être négatif');
        }

        if (
            $budget->getDateModification() !== null
            && $budget->getDateAllocation() !== null
            && $budget->getDateModification() < $budget->getDateAllocation()
        ) {
            throw new \InvalidArgumentException('La date de modification doit être postérieure à la date d\'allocation');
        }

        return true;
    }

    /**
     * Check whether the budget has been exceeded.
     */
    public function isDepassement(Budget $budget): bool
    {
        return $budget->getMontantUtilise() > $budget->getMontantAlloue();
    }

    /**
     * Calculate the remaining budget amount.
     */
    public function getMontantRestant(Budget $budget): float
    {
        return $budget->getMontantAlloue() - $budget->getMontantUtilise();
    }

    /**
     * Calculate the usage percentage.
     */
    public function getPourcentageUtilisation(Budget $budget): float
    {
        if ($budget->getMontantAlloue() == 0) {
            return 0;
        }

        return round(($budget->getMontantUtilise() / $budget->getMontantAlloue()) * 100, 2);
    }
}
