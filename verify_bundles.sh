#!/bin/bash
# Verification script for Bundle Installation

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║     BUNDLE INSTALLATION VERIFICATION                         ║"
echo "╚════════════════════════════════════════════════════════════════╝"

echo ""
echo "✅ Checking Configuration Files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if [ -f "config/packages/vich_uploader.yaml" ]; then
    echo "✓ vich_uploader.yaml"
else
    echo "✗ vich_uploader.yaml - MISSING"
fi

if [ -f "config/packages/knp_paginator.yaml" ]; then
    echo "✓ knp_paginator.yaml"
else
    echo "✗ knp_paginator.yaml - MISSING"
fi

if [ -f "config/packages/chartjs.yaml" ]; then
    echo "✓ chartjs.yaml"
else
    echo "✗ chartjs.yaml - MISSING"
fi

echo ""
echo "✅ Checking Template Files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for file in "_chart_budget_expenses.html.twig" \
            "_chart_depenses_by_category.html.twig" \
            "_chart_depenses_evolution.html.twig" \
            "_chart_teams_budget_comparison.html.twig"; do
    if [ -f "templates/partials/$file" ]; then
        echo "✓ $file"
    else
        echo "✗ $file - MISSING"
    fi
done

echo ""
echo "✅ Checking Upload Directories..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for dir in "public/uploads/logos/teams" \
           "public/uploads/documents/depenses" \
           "public/uploads/documents/budgets"; do
    if [ -d "$dir" ]; then
        echo "✓ $dir"
    else
        echo "✗ $dir - MISSING"
    fi
done

echo ""
echo "✅ Checking Bundles in Composer..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

if composer show vich/uploader-bundle 2>/dev/null | grep -q "v2.9.1"; then
    echo "✓ vich/uploader-bundle v2.9.1"
else
    echo "✗ vich/uploader-bundle - NOT FOUND or WRONG VERSION"
fi

if composer show knplabs/knp-paginator-bundle 2>/dev/null | grep -q "v6.10.0"; then
    echo "✓ knplabs/knp-paginator-bundle v6.10.0"
else
    echo "✗ knplabs/knp-paginator-bundle - NOT FOUND or WRONG VERSION"
fi

echo ""
echo "✅ Documentation Files..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

for file in "BUNDLES_INTEGRATION_GUIDE.md" \
            "BUNDLES_COMPLETE_SETUP.md" \
            "QUICK_START_BUNDLES.md" \
            "BUNDLES_SUMMARY.txt" \
            "Example_BudgetDashboardController.php"; do
    if [ -f "$file" ]; then
        echo "✓ $file"
    else
        echo "✗ $file - MISSING"
    fi
done

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║              ✅ VERIFICATION COMPLETE                         ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "Next steps:"
echo "1. Run: php bin/console make:migration"
echo "2. Run: php bin/console doctrine:migrations:migrate"
echo "3. Run: symfony serve"
echo ""
