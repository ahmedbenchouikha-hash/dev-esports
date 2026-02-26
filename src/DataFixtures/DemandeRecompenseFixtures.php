<?php

namespace App\DataFixtures;

use App\Entity\DemandeRecompense;
use App\Entity\Recompense;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DemandeRecompenseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Récupérer une récompense existante (créer une basique si elle n'existe pas)
        $recompenses = $manager->getRepository(Recompense::class)->findAll();
        if (empty($recompenses)) {
            $recompense = new Recompense();
            $recompense->setNom('OR - Excellence Sportive');
            $recompense->setValue(1000);
            $manager->persist($recompense);
            $manager->flush();
        } else {
            $recompense = $recompenses[0];
        }

        // Demande 1: SOUMISE (légit, score IA 85)
        $demande1 = new DemandeRecompense();
        $demande1->setNomDemandeur('Jules Dupont');
        $demande1->setEmail('jules.dupont@example.com');
        $demande1->setRecompense($recompense);
        $demande1->setMotif('J\'ai remporté le championnat régional d\'esports 2026 en tant que team leader. Performance exceptionnelle avec 18/5 en stats personnelles, mentorat des jeunes joueurs et fair-play remarqué par les arbitres.');
        $demande1->setStatut('soumise');
        $demande1->setAiLegitimacyScore(85);
        $demande1->setAiFraudType('legitimate');
        $demande1->setAiConfidenceLevel(92.5);
        $demande1->setAiKeyPoints(['Performance exceptionnelle', 'Fair-play remarqué', 'Mentorat d\'autres joueurs', 'Leadership']);
        $demande1->setAiSentiment('positive');
        $demande1->setAiSuggestedRewardType('Excellence Sportive');
        $demande1->setAiAnalysisReason('Demande convaincante avec preuves claires de performance supérieure et comportement exemplaire.');
        $demande1->setAiShouldAutoApprove(false);
        $demande1->setAiAnalyzedAt(new \DateTime('2026-02-20 10:00:00'));
        $manager->persist($demande1);

        // Demande 2: PRE_ANALYSEE (suspect, score IA 42)
        $demande2 = new DemandeRecompense();
        $demande2->setNomDemandeur('Marie Smith');
        $demande2->setEmail('marie.smith@example.com');
        $demande2->setRecompense($recompense);
        $demande2->setMotif('J\'ai vraiment bien joué hier.');
        $demande2->setStatut('pre_analysee');
        $demande2->setAiLegitimacyScore(42);
        $demande2->setAiFraudType('suspicious');
        $demande2->setAiConfidenceLevel(68.0);
        $demande2->setAiKeyPoints(['Motif vague', 'Pas de détails', 'Pas de preuves']);
        $demande2->setAiSentiment('neutral');
        $demande2->setAiSuggestedRewardType('À vérifier');
        $demande2->setAiAnalysisReason('Motif insuffisant et manque de détails. Recommande de demander plus de preuves.');
        $demande2->setAiShouldAutoApprove(false);
        $demande2->setAiAnalyzedAt(new \DateTime('2026-02-21 14:30:00'));
        $manager->persist($demande2);

        // Demande 3: EN_REVISION (fraud, score IA 15)
        $demande3 = new DemandeRecompense();
        $demande3->setNomDemandeur('BadGuy Player');
        $demande3->setEmail('badguy@example.com');
        $demande3->setRecompense($recompense);
        $demande3->setMotif('Envoyez-moi mille euros ou j\'utilise des scripts. Vous allez regretter!');
        $demande3->setStatut('en_revision');
        $demande3->setAiLegitimacyScore(15);
        $demande3->setAiFraudType('spam');
        $demande3->setAiConfidenceLevel(99.0);
        $demande3->setAiKeyPoints(['Tentative d\'extorsion', 'Menaces', 'Pas de motif légitime']);
        $demande3->setAiSentiment('negative');
        $demande3->setAiSuggestedRewardType('REJETER');
        $demande3->setAiAnalysisReason('Tentative manifeste de fraude et d\'extorsion. À signaler aux autorités.');
        $demande3->setAiShouldAutoApprove(false);
        $demande3->setAiAnalyzedAt(new \DateTime('2026-02-22 09:15:00'));
        $manager->persist($demande3);

        // Demande 4: APPROUVEE (score IA 90)
        $demande4 = new DemandeRecompense();
        $demande4->setNomDemandeur('Thomas Laurent');
        $demande4->setEmail('thomas.laurent@example.com');
        $demande4->setRecompense($recompense);
        $demande4->setMotif('Victoire aux qualifications nationales avec performances exceptionnelles: MVP du tournament, 22/3 en finale. Exemplaire fair-play et coaching constant des nouveaux joueurs.');
        $demande4->setStatut('approuvée');
        $demande4->setAiLegitimacyScore(90);
        $demande4->setAiFraudType('legitimate');
        $demande4->setAiConfidenceLevel(95.0);
        $demande4->setAiKeyPoints(['MVP du tournament', 'Performance exceptionnelle', 'Fair-play exemplaire', 'Mentorat']);
        $demande4->setAiSentiment('positive');
        $demande4->setAiSuggestedRewardType('Excellence Sportive');
        $demande4->setAiAnalysisReason('Demande exceptionnel avec preuves impressionnantes.');
        $demande4->setAiShouldAutoApprove(true);
        $demande4->setIsPrioritaire(true);
        $demande4->setAiAnalyzedAt(new \DateTime('2026-02-15 11:00:00'));
        $manager->persist($demande4);

        // Demande 5: REJETEE (score IA 25)
        $demande5 = new DemandeRecompense();
        $demande5->setNomDemandeur('NoRegrets User');
        $demande5->setEmail('noregrets@example.com');
        $demande5->setRecompense($recompense);
        $demande5->setMotif('1000 fois pareil, vous êtes tous nuls et vous me devez une récompense!!!');
        $demande5->setStatut('rejetée');
        $demande5->setAiLegitimacyScore(25);
        $demande5->setAiFraudType('suspicious');
        $demande5->setAiConfidenceLevel(85.0);
        $demande5->setAiKeyPoints(['Abus de langage', 'Insultes', 'Demande répétée']);
        $demande5->setAiSentiment('negative');
        $demande5->setAiSuggestedRewardType('Rejeter');
        $demande5->setAiAnalysisReason('Comportement toxique détecté. Raison juste de rejeter.');
        $demande5->setAiShouldAutoApprove(false);
        $demande5->setAiAnalyzedAt(new \DateTime('2026-02-19 16:45:00'));
        $manager->persist($demande5);

        // Demande 6: SOUMISE (légitime, score IA 78)
        $demande6 = new DemandeRecompense();
        $demande6->setNomDemandeur('Emma Garcia');
        $demande6->setEmail('emma.garcia@example.com');
        $demande6->setRecompense($recompense);
        $demande6->setMotif('Participation au championnat international 2026, qualification pour les demi-finales avec 65% win-rate. Super communication et leadership du team.');
        $demande6->setStatut('soumise');
        $demande6->setAiLegitimacyScore(78);
        $demande6->setAiFraudType('legitimate');
        $demande6->setAiConfidenceLevel(88.0);
        $demande6->setAiKeyPoints(['Qualification internationale', 'Bonne win-rate', 'Leadership confirmé']);
        $demande6->setAiSentiment('positive');
        $demande6->setAiSuggestedRewardType('Excellence Sportive');
        $demande6->setAiAnalysisReason('Demande solide avec accomplissements significatifs.');
        $demande6->setAiShouldAutoApprove(false);
        $demande6->setAiAnalyzedAt(new \DateTime('2026-02-18 13:20:00'));
        $manager->persist($demande6);

        $manager->flush();
    }
}
