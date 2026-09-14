<?php

namespace App\DataFixtures;

use App\Entity\Page;
use App\Entity\Bloc;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // ============================================
        // 1. PAGE D'ACCUEIL (sans articles)
        // ============================================
        $home = $this->createPage('accueil', 'Accueil', 'home', null, 0);
        $home->addBloc($this->createBloc('titre', [
            'niveau' => 'h1',
            'texte' => 'Bienvenue sur le site de Marion&Co',
            'class' => 'text-center display-3'
        ], 0, 1, null, null, 1));  // Page principale, Bloc 1
        
        $home->addBloc($this->createBloc('paragraphe', [
            'texte' => 'Artiste polyvalente, elle vous invite à découvrir ses multiples activités : philosophie, musique, marionnettes et thérapie.',
            'class' => 'lead text-center'
        ], 0, 1, null, null, 2));  // Page principale, Bloc 2
        
        $home->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/home/philosophe.jpg', 'alt' => 'Philosophie', 'legende' => 'Philosophe'],
                ['url' => '/uploads/home/musicienne.jpg', 'alt' => 'Musicienne', 'legende' => 'Musicienne'],
                ['url' => '/uploads/home/marionnettes.jpg', 'alt' => 'Marionnettiste', 'legende' => 'Marionnettiste'],
                ['url' => '/uploads/home/therapeute.jpg', 'alt' => 'Thérapeute', 'legende' => 'Thérapeute']
            ],
            'disposition' => 'grille'
        ], 0, 4, null, null, 3));  // Page principale, Bloc 3
        $manager->persist($home);

        // ============================================
        // 2. SECTION PHILOSOPHIE
        // ============================================
        $philosophie = $this->createPage('philosophie', 'Philosophie', 'philosophe', null, 0);
        $philosophie->addBloc($this->createBloc('titre', [
            'niveau' => 'h1',
            'texte' => 'Philosophie',
            'class' => 'text-center'
        ], 0, 1, null, null, 1));  // Page principale, Bloc 1
        
        $philosophie->addBloc($this->createBloc('paragraphe', [
            'texte' => "La philosophie est une discipline qui permet de réfléchir sur le monde, l'existence et la condition humaine. Découvrez mes formations, conférences, enseignements et écrits.",
            'class' => 'lead text-center'
        ], 0, 1, null, null, 2));  // Page principale, Bloc 2
        $manager->persist($philosophie);
        $manager->flush(); // Pour que les sous-pages puissent référencer l'id du parent

        // 2.1 Formation (statique)
        $formation = $this->createPage('philosophe-formation', 'Formation en Philosophie', 'philosophe', 'statique', 0, $philosophie);
        
        // Article 1 : Présentation de la formation
        $formation->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Ma formation en philosophie',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $formation->addBloc($this->createBloc('paragraphe', [
            'texte' => "J'ai étudié la philosophie à l'université de Paris-Sorbonne où j'ai obtenu un master en philosophie contemporaine. Mes recherches portent sur l'éthique et la philosophie politique.\n\nJe continue à me former régulièrement à travers des séminaires et des colloques internationaux.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        $formation->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/philosophe/formation1.jpg', 'alt' => 'Cours de philosophie', 'legende' => 'Cours à l\'université'],
                ['url' => '/uploads/philosophe/formation2.jpg', 'alt' => 'Bibliothèque', 'legende' => 'La bibliothèque universitaire']
            ],
            'disposition' => 'horizontal'
        ], 0, 2, null, 1, 3));  // Article 1, Bloc 3
        
        // Article 2 : Diplômes et certifications
        $formation->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Diplômes et certifications',
            'class' => 'text-secondary mt-5'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $formation->addBloc($this->createBloc('paragraphe', [
            'texte' => "• Master en philosophie contemporaine - Université Paris-Sorbonne (2018)\n• Licence en philosophie - Université Paris-Sorbonne (2016)\n• Certificat en éthique appliquée - Université de Genève (2020)\n• Formation continue en philosophie pratique (2022)",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        $manager->persist($formation);

        // 2.2 Conférences (liste)
        $conferences = $this->createPage('philosophe-conferences', 'Conférences de philosophie', 'philosophe', 'conference_liste', 0, $philosophie);
        
        // Article 1 : Introduction
        $conferences->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Mes conférences',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $conferences->addBloc($this->createBloc('paragraphe', [
            'texte' => "Retrouvez ici la liste de mes prochaines conférences et celles déjà données.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Conférences à venir
        $conferences->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Prochaines conférences',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $conferences->addBloc($this->createBloc('paragraphe', [
            'texte' => "📅 15 octobre 2026 - Paris : 'L\'éthique à l\'ère du numérique'\n📅 20 novembre 2026 - Lyon : 'La philosophie du quotidien'\n📅 10 janvier 2027 - Bruxelles : 'Penser le monde d\'après'",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Conférences passées
        $conferences->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Conférences passées',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $conferences->addBloc($this->createBloc('paragraphe', [
            'texte' => "✅ 15 mars 2026 - Bordeaux : 'Socrate et la pensée critique'\n✅ 20 janvier 2026 - Toulouse : 'La philosophie stoïcienne moderne'\n✅ 10 novembre 2025 - Marseille : 'La quête du sens aujourd\'hui'",
            'class' => 'fs-5'
        ], 0, 1, null, 3, 2));  // Article 3, Bloc 2
        $manager->persist($conferences);

        // 2.3 Conférence détail
        $conferenceDetail = $this->createPage('philosophe-conference-1', 'Le sens de la vie selon Nietzsche', 'philosophe', 'conference_detail', 0);
        
        // Article 1 : Introduction
        $conferenceDetail->addBloc($this->createBloc('titre', [
            'niveau' => 'h1',
            'texte' => 'Le sens de la vie selon Nietzsche',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $conferenceDetail->addBloc($this->createBloc('paragraphe', [
            'texte' => "Conférence donnée le 15 mai 2026 à l'université de Paris-Sorbonne.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Résumé
        $conferenceDetail->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Résumé de la conférence',
            'class' => 'text-primary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $conferenceDetail->addBloc($this->createBloc('paragraphe', [
            'texte' => "Nietzsche nous invite à repenser le sens de la vie à travers sa philosophie de la volonté de puissance et de l'éternel retour. Cette conférence explore comment ces concepts peuvent nous aider à donner du sens à notre existence moderne.\n\nLa question fondamentale est : comment vivre pleinement dans un monde où Dieu est mort ? Nietzsche propose de créer nos propres valeurs et d'affirmer la vie avec passion.",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Vidéo
        $conferenceDetail->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Extrait de la conférence',
            'class' => 'text-primary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $conferenceDetail->addBloc($this->createBloc('video', [
            'url' => 'https://www.youtube.com/embed/VUg_BipVde4?si=dfjoDWp9hX2HuBSt',
            'legende' => 'Extrait de la conférence sur Nietzsche'
        ], 0, 1, null, 3, 2));  // Article 3, Bloc 2
        $manager->persist($conferenceDetail);

        // 2.4 Enseignement (statique)
        $enseignement = $this->createPage('philosophe-enseignement', "Enseignement de la philosophie", 'philosophe', 'statique', 0, $philosophie);
        
        // Article 1 : Présentation
        $enseignement->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => "J'enseigne la philosophie",
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $enseignement->addBloc($this->createBloc('paragraphe', [
            'texte' => "Je propose des cours de philosophie à tous les niveaux : lycée, université, et adultes en formation continue.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Thèmes abordés
        $enseignement->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Thèmes abordés',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $enseignement->addBloc($this->createBloc('paragraphe', [
            'texte' => "Mes cours abordent les grands thèmes de la philosophie occidentale :\n\n• L'éthique et la morale\n• La métaphysique\n• L'épistémologie (théorie de la connaissance)\n• L'esthétique\n• La philosophie politique\n• La logique et l'argumentation",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Liens utiles
        $enseignement->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Liens utiles',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $enseignement->addBloc($this->createBloc('liens', [
            'liens' => [
                ['url' => '/contact', 'texte' => 'Me contacter pour un cours', 'icone' => 'envelope'],
                ['url' => '/uploads/cours/programme.pdf', 'texte' => 'Télécharger le programme', 'icone' => 'file-pdf'],
                ['url' => '/uploads/cours/bibliographie.pdf', 'texte' => 'Bibliographie', 'icone' => 'book']
            ]
        ], 0, 1, null, 3, 2));  // Article 3, Bloc 2
        $manager->persist($enseignement);

        // 2.5 Écrits (dynamique)
        $ecrits = $this->createPage('philosophe-ecrits', 'Écrits philosophiques', 'philosophe', 'ecrits_liste', 0, $philosophie);
        
        // Article 1 : Introduction
        $ecrits->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Mes écrits',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $ecrits->addBloc($this->createBloc('paragraphe', [
            'texte' => "Découvrez mes articles, essais et publications en philosophie.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Liste des écrits
        $ecrits->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Publications récentes',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $ecrits->addBloc($this->createBloc('paragraphe', [
            'texte' => "📖 'L\'éthique du care dans la philosophie contemporaine' - Revue de philosophie, 2025\n📖 'La démocratie participative à l\'ère numérique' - Essai, 2024\n📖 'Socrate, une méthode pour aujourd\'hui' - Article, 2024",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        $manager->persist($ecrits);

        // ============================================
        // 3. SECTION MUSICIENNE
        // ============================================
        $musicienne = $this->createPage('musicienne', 'Musicienne', 'musicienne', null, 0);
        $musicienne->addBloc($this->createBloc('titre', [
            'niveau' => 'h1',
            'texte' => 'Musicienne',
            'class' => 'text-center'
        ], 0, 1, null, null, 1));  // Page principale, Bloc 1
        
        $musicienne->addBloc($this->createBloc('paragraphe', [
            'texte' => "La musique est une passion qui m'anime sous différentes formes : pianiste, organiste et cheffe de chœur.",
            'class' => 'lead text-center'
        ], 0, 1, null, null, 2));  // Page principale, Bloc 2
        $manager->persist($musicienne);
        $manager->flush(); // Pour que les sous-pages puissent référencer l'id du parent

        // 3.1 Pianiste (section)
        $pianiste = $this->createPage('musicienne-pianiste', 'Pianiste', 'musicienne', 'section', 0, $musicienne);
        $pianiste->setMenuWithoutLink(true);
        
        // Article 1 : Présentation
        $pianiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Pianiste',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $pianiste->addBloc($this->createBloc('paragraphe', [
            'texte' => "Le piano est mon instrument de prédilection. Je le pratique depuis mon enfance et j'ai eu la chance de me former auprès de grands maîtres.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Parcours
        $pianiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Mon parcours',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $pianiste->addBloc($this->createBloc('paragraphe', [
            'texte' => "J'ai commencé le piano à l'âge de 6 ans. J'ai obtenu mon Diplôme d'Études Musicales au Conservatoire de Paris et j'ai suivi des masterclasses avec des pianistes renommés.\n\nDepuis 2015, je me produis en concert en France et à l'étranger.",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Photos
        $pianiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'En images',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $pianiste->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/musicienne/piano1.jpg', 'alt' => 'Au piano', 'legende' => 'Mon piano à queue'],
                ['url' => '/uploads/musicienne/piano2.jpg', 'alt' => 'Concert', 'legende' => 'Concert à la Salle Pleyel']
            ],
            'disposition' => 'horizontal'
        ], 0, 2, null, 3, 2));  // Article 3, Bloc 2
        $manager->persist($pianiste);
        $manager->flush(); // Pour que les sous-pages puissent référencer l'id du parent

        // 3.1.1 Formation pianiste (statique)
        $formationPiano = $this->createPage('musicienne-pianiste-formation', 'Formation pianistique', 'musicienne', 'statique', 0, $pianiste, 'pianiste');
        
        // Article 1 : Formation
        $formationPiano->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Ma formation au piano',
            'class' => 'text-secondary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $formationPiano->addBloc($this->createBloc('paragraphe', [
            'texte' => "J'ai commencé le piano à l'âge de 6 ans. J'ai obtenu mon Diplôme d'Études Musicales au Conservatoire de Paris et j'ai suivi des masterclasses avec des pianistes renommés.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Concours et prix
        $formationPiano->addBloc($this->createBloc('titre', [
            'niveau' => 'h4',
            'texte' => 'Concours et prix',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $formationPiano->addBloc($this->createBloc('paragraphe', [
            'texte' => "🏆 Premier prix du Concours International de Piano de Lyon (2018)\n🏆 Finaliste du Concours de Paris (2016)\n🏅 Médaille d'or du Conservatoire de Paris (2015)",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Photos
        $formationPiano->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/musicienne/piano1.jpg', 'alt' => 'Au piano', 'legende' => 'Mon piano à queue'],
                ['url' => '/uploads/musicienne/piano2.jpg', 'alt' => 'Concert', 'legende' => 'Concert à la Salle Pleyel']
            ],
            'disposition' => 'horizontal'
        ], 0, 2, null, 3, 1));  // Article 3, Bloc 1
        $manager->persist($formationPiano);

        // 3.1.2 Répertoire Solo (dynamique) - AVEC subSection 'pianiste'
        $repertoireSolo = $this->createPage('musicienne-pianiste-repertoire-solo', 'Répertoire Solo', 'musicienne', 'repertoire_solo', 0, $pianiste, 'pianiste');
        
        // Article 1 : Introduction
        $repertoireSolo->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Mon répertoire solo',
            'class' => 'text-secondary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $repertoireSolo->addBloc($this->createBloc('paragraphe', [
            'texte' => "Découvrez les œuvres que je joue en concert en tant que soliste.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Œuvres principales
        $repertoireSolo->addBloc($this->createBloc('titre', [
            'niveau' => 'h4',
            'texte' => 'Œuvres principales',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $repertoireSolo->addBloc($this->createBloc('paragraphe', [
            'texte' => "🎵 J.S. Bach - Variations Goldberg\n🎵 L. van Beethoven - Sonate Clair de lune\n🎵 F. Chopin - Ballade n°1\n🎵 F. Liszt - Liebesträume\n🎵 C. Debussy - Préludes",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        $manager->persist($repertoireSolo);

        // 3.1.3 Répertoire Duo (dynamique) - AVEC subSection 'pianiste'
        $repertoireDuo = $this->createPage('musicienne-pianiste-repertoire-duo', 'Répertoire Duo', 'musicienne', 'repertoire_duo', 0, $pianiste, 'pianiste');
        
        // Article 1 : Introduction
        $repertoireDuo->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Mon répertoire en duo',
            'class' => 'text-secondary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $repertoireDuo->addBloc($this->createBloc('paragraphe', [
            'texte' => "J'apprécie particulièrement le jeu en duo. Voici les œuvres que j'interprète avec d'autres musiciens.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Œuvres en duo
        $repertoireDuo->addBloc($this->createBloc('titre', [
            'niveau' => 'h4',
            'texte' => 'Œuvres en duo',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $repertoireDuo->addBloc($this->createBloc('paragraphe', [
            'texte' => "🎵 W.A. Mozart - Sonate pour piano à 4 mains\n🎵 F. Schubert - Fantasie en fa mineur\n🎵 C. Debussy - Petite Suite\n🎵 G. Fauré - Dolly Suite\n🎵 M. Ravel - Ma mère l'Oye",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        $manager->persist($repertoireDuo);

        // 3.2 Organiste (dynamique) - AVEC subSection 'organiste'
        $organiste = $this->createPage('musicienne-organiste', 'Organiste', 'musicienne', 'organiste', 0, $musicienne, 'organiste');
        
        // Article 1 : Présentation
        $organiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Organiste',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $organiste->addBloc($this->createBloc('paragraphe', [
            'texte' => "L'orgue est un instrument fascinant que je pratique régulièrement. Je suis organiste titulaire à l'église de Saint-Germain-des-Prés et je donne des concerts d'orgue dans toute la France.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Répertoire
        $organiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Mon répertoire d\'orgue',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $organiste->addBloc($this->createBloc('paragraphe', [
            'texte' => "🎵 J.S. Bach - Toccata et fugue en ré mineur\n🎵 L. Vierne - Symphonie pour orgue n°1\n🎵 C. Franck - Trois chorals\n🎵 M. Duruflé - Prélude et fugue sur le nom d'Alain\n🎵 G. Fauré - Préludes pour orgue",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Photos
        $organiste->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/musicienne/orgue1.jpg', 'alt' => 'À l\'orgue', 'legende' => 'À l\'orgue de Saint-Germain-des-Prés'],
                ['url' => '/uploads/musicienne/orgue2.jpg', 'alt' => 'Concert d\'orgue', 'legende' => 'Concert à Notre-Dame']
            ],
            'disposition' => 'horizontal'
        ], 0, 2, null, 3, 1));  // Article 3, Bloc 1
        $manager->persist($organiste);

        // 3.3 Cheffe de chœur (dynamique) - AVEC subSection 'chef_de_choeur'
        $chefChoeur = $this->createPage('musicienne-chef-de-choeur', 'Cheffe de chœur', 'musicienne', 'chef_de_choeur', 0, $musicienne, 'chef_de_choeur');
        
        // Article 1 : Présentation
        $chefChoeur->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Cheffe de chœur',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $chefChoeur->addBloc($this->createBloc('paragraphe', [
            'texte' => "Je dirige le chœur de l'Église Saint-Germain-des-Prés depuis 2020. Nous interprétons un répertoire varié allant de la musique sacrée au chant choral contemporain.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Répertoire choral
        $chefChoeur->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Répertoire du chœur',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $chefChoeur->addBloc($this->createBloc('paragraphe', [
            'texte' => "🎵 G. Fauré - Requiem\n🎵 J.S. Bach - Motets\n🎵 W.A. Mozart - Messe en ut mineur\n🎵 F. Poulenc - Gloria\n🎵 A. Bruckner - Motets",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Vidéo
        $chefChoeur->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Le chœur en action',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $chefChoeur->addBloc($this->createBloc('video', [
            'url' => 'https://www.youtube.com/embed/VUg_BipVde4?si=dfjoDWp9hX2HuBSt',
            'legende' => 'Le chœur en répétition'
        ], 0, 1, null, 3, 2));  // Article 3, Bloc 2
        $manager->persist($chefChoeur);

        // ============================================
        // 4. MARIONNETTISTE (dynamique)
        // ============================================
        $marionnettiste = $this->createPage('marionnettiste', 'Marionnettiste', 'marionnettiste', 'spectacles', 0);
        
        // Article 1 : Introduction
        $marionnettiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h1',
            'texte' => 'Marionnettiste',
            'class' => 'text-center'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $marionnettiste->addBloc($this->createBloc('paragraphe', [
            'texte' => "La marionnette est un art qui me passionne. Je crée mes propres marionnettes et je donne des spectacles pour petits et grands.",
            'class' => 'lead text-center'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Spectacles
        $marionnettiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Mes spectacles',
            'class' => 'text-primary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $marionnettiste->addBloc($this->createBloc('paragraphe', [
            'texte' => "🎭 'Les aventures de Pinocchio' - Un spectacle interactif pour enfants\n🎭 'Le Petit Prince en marionnettes' - Une adaptation poétique\n🎭 'Contes et légendes' - Un voyage à travers les histoires du monde",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Images
        $marionnettiste->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Créations',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $marionnettiste->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/marionnettes/spectacle1.jpg', 'alt' => 'Spectacle de marionnettes', 'legende' => 'Spectacle "Les aventures de Pinocchio"'],
                ['url' => '/uploads/marionnettes/spectacle2.jpg', 'alt' => 'Création de marionnettes', 'legende' => 'Atelier de fabrication']
            ],
            'disposition' => 'horizontal'
        ], 0, 2, null, 3, 2));  // Article 3, Bloc 2
        $manager->persist($marionnettiste);

        // ============================================
        // 5. SECTION THÉRAPEUTE
        // ============================================
        $therapeute = $this->createPage('therapeute', 'Thérapeute', 'therapeute', null, 0);
        
        // Article 1 : Introduction
        $therapeute->addBloc($this->createBloc('titre', [
            'niveau' => 'h1',
            'texte' => 'Thérapeute',
            'class' => 'text-center'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $therapeute->addBloc($this->createBloc('paragraphe', [
            'texte' => "Je pratique une approche thérapeutique unique qui allie philosophie et bien-être.",
            'class' => 'lead text-center'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        $manager->persist($therapeute);
        $manager->flush(); // Pour que les sous-pages puissent référencer l'id du parent

        // 5.1 Méthode MARION (statique)
        $methodeMarion = $this->createPage('therapeute-methode-marion', 'Méthode MARION', 'therapeute', 'statique', 0, $therapeute);
        
        // Article 1 : Présentation
        $methodeMarion->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'La méthode MARION',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $methodeMarion->addBloc($this->createBloc('paragraphe', [
            'texte' => "La méthode MARION est une approche thérapeutique innovante qui combine :",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Les piliers
        $methodeMarion->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Les quatre piliers',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $methodeMarion->addBloc($this->createBloc('paragraphe', [
            'texte' => "🧠 La philosophie pour comprendre le sens de l'existence\n🎵 La musique pour apaiser et harmoniser\n🎭 La marionnette pour libérer l'expression corporelle\n💆 La thérapie pour accompagner vers le bien-être\n\nCette méthode a été développée suite à 10 années de pratique et de recherche.",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Bénéfices
        $methodeMarion->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Les bénéfices',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $methodeMarion->addBloc($this->createBloc('paragraphe', [
            'texte' => "✅ Réduction du stress et de l'anxiété\n✅ Meilleure compréhension de soi\n✅ Développement de la créativité\n✅ Amélioration de la communication\n✅ Renforcement de la confiance en soi",
            'class' => 'fs-5'
        ], 0, 1, null, 3, 2));  // Article 3, Bloc 2
        
        // Article 4 : Images
        $methodeMarion->addBloc($this->createBloc('images_groupe', [
            'images' => [
                ['url' => '/uploads/therapeute/methode1.jpg', 'alt' => 'Atelier MARION', 'legende' => 'Atelier en plein air'],
                ['url' => '/uploads/therapeute/methode2.jpg', 'alt' => 'Pratique thérapeutique', 'legende' => 'Séance individuelle']
            ],
            'disposition' => 'horizontal'
        ], 0, 2, null, 4, 1));  // Article 4, Bloc 1
        $manager->persist($methodeMarion);

        // 5.2 Prochains ateliers (dynamique)
        $ateliers = $this->createPage('therapeute-ateliers', 'Prochains ateliers', 'therapeute', 'ateliers_liste', 0, $therapeute);
        
        // Article 1 : Introduction
        $ateliers->addBloc($this->createBloc('titre', [
            'niveau' => 'h2',
            'texte' => 'Prochains ateliers',
            'class' => 'text-primary'
        ], 0, 1, null, 1, 1));  // Article 1, Bloc 1
        
        $ateliers->addBloc($this->createBloc('paragraphe', [
            'texte' => "Je propose régulièrement des ateliers pour découvrir la méthode MARION.",
            'class' => 'fs-5'
        ], 0, 1, null, 1, 2));  // Article 1, Bloc 2
        
        // Article 2 : Calendrier
        $ateliers->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Calendrier 2026',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 2, 1));  // Article 2, Bloc 1
        
        $ateliers->addBloc($this->createBloc('paragraphe', [
            'texte' => "📅 15-16 octobre 2026 - Paris : Atelier découverte\n📅 20-21 novembre 2026 - Lyon : Initiation à la méthode MARION\n📅 10-11 décembre 2026 - Bruxelles : Approfondissement\n📅 15-16 janvier 2027 - Genève : Formation complète",
            'class' => 'fs-5'
        ], 0, 1, null, 2, 2));  // Article 2, Bloc 2
        
        // Article 3 : Inscription
        $ateliers->addBloc($this->createBloc('titre', [
            'niveau' => 'h3',
            'texte' => 'Inscription',
            'class' => 'text-secondary mt-4'
        ], 0, 1, null, 3, 1));  // Article 3, Bloc 1
        
        $ateliers->addBloc($this->createBloc('paragraphe', [
            'texte' => "Les places sont limitées à 12 participants par atelier.",
            'class' => 'fs-5'
        ], 0, 1, null, 3, 2));  // Article 3, Bloc 2
        
        $ateliers->addBloc($this->createBloc('liens', [
            'liens' => [
                ['url' => '/contact', 'texte' => 'Réserver ma place', 'icone' => 'envelope-paper'],
                ['url' => '/uploads/ateliers/programme.pdf', 'texte' => 'Télécharger le programme complet', 'icone' => 'file-pdf']
            ]
        ], 0, 1, null, 3, 3));  // Article 3, Bloc 3
        $manager->persist($ateliers);

        // ============================================
        // SAUVEGARDE
        // ============================================
        $manager->flush();
    }

    // ============================================
    // MÉTHODES UTILITAIRES
    // ============================================

    private function createPage(
        string $slug,
        string $title,
        string $section,
        ?string $type = null,
        int $position = 0,
        ?Page $parent = null,
        ?string $subSection = null
    ): Page {
        $page = new Page();
        $page->setSlug($slug);
        $page->setTitle($title);
        $page->setSection($section);
        $page->setType($type);
        $page->setPosition($position);
        $page->setIsActive(true);
        $page->setSubSection($subSection);
        
        if ($parent) {
            $page->setParentId($parent->getId());
        }
        
        return $page;
    }

    private function createBloc(
        string $type,
        array $content,
        int $position = 0,
        int $colonnes = 1,
        ?string $cssClass = null,
        ?int $articleNumber = null,
        ?int $blocNumber = null
    ): Bloc {
        $bloc = new Bloc();
        $bloc->setType($type);
        $bloc->setContentFromArray($content);
        $bloc->setPosition($position);
        $bloc->setColonnes($colonnes);
        $bloc->setArticleNumber($articleNumber);
        $bloc->setBlocNumber($blocNumber);
        
        if ($cssClass) {
            $bloc->setCssClass($cssClass);
        }
        
        return $bloc;
    }
}