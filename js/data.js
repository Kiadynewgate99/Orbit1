/* =========================================================================
   ClubHub v3 - Data
   ========================================================================= */

const CLUB_CATEGORIES = [
  { id: 'tech', label: 'Technologie', icon: '◈', color: '#ff4502' },
  { id: 'culture', label: 'Culture & Arts', icon: '◇', color: '#dcb8ff' },
  { id: 'sport', label: 'Sport', icon: '△', color: '#c2ff01' },
  { id: 'entrepreneuriat', label: 'Entrepreneuriat', icon: '◊', color: '#0044ff' },
  { id: 'environnement', label: 'Environnement', icon: '◉', color: '#10ffa1' },
  { id: 'debats', label: 'Débats & Société', icon: '◆', color: '#dcb8ff' },
  { id: 'benevolat', label: 'Bénévolat', icon: '○', color: '#0044ff' },
  { id: 'langues', label: 'Langues', icon: '◯', color: '#ff4502' },
];

const CLUBS = [
   { id: 'club-001', name: 'Club Informatique', domain: 'tech', icon: 'CI', color: '#ff4502', description: 'Promouvoir les competences numeriques et les projets etudiants', longDescription: 'Le Club Informatique rassemble les passionnes de technologie de l\'universite. Nous organisons regulierement des ateliers pratiques, des conférences avec des professionnels du secteur, et des hackathons inter-universitaires.', members: 87, events: 24, founded: 2018, president: 'Andry RAZAFY', email: 'info@univ-clubinfo.mg', location: 'Bâtiment C - Salle 204', meeting: 'Mercredi 18h-20h', tags: ['Python', 'IA', 'Hackathon', 'Web', 'Cybersécurité'], activities: ['Ateliers Python', 'Hackathons', 'Conférences tech', 'Projets open source'], open: true, logo: null, responsible_id: 4 },
   { id: 'club-002', name: 'Club Débat & Éloquence', domain: 'debats', icon: 'CD', color: '#dcb8ff', description: 'Affûter votre esprit critique et votre prise de parole', longDescription: 'Le Club Débat & Éloquence développe les capacités d\'argumentation, de persuasion et d\'écoute active.', members: 54, events: 18, founded: 2019, president: 'Sarah ANDRIANARIVO', email: 'debate@univ.mg', location: 'Amphithéâtre B', meeting: 'Samedi 14h-17h', tags: ['Éloquence', 'Argumentation', 'Leadership', 'Rhétorique'], activities: ['Débats contradictoires', 'Concours d\'éloquence', 'Conférences'], open: true, logo: null, responsible_id: 5 },
   { id: 'club-003', name: 'Club Arts Martiaux', domain: 'sport', icon: 'AM', color: '#c2ff01', description: 'Karaté, judo, taekwondo. Entraînements réguliers', longDescription: 'Le Club Arts Martiaux propose la pratique de plusieurs disciplines : karaté, judo et taekwondo.', members: 42, events: 32, founded: 2017, president: 'Mamy RAZAFINDRAKOTO', email: 'martiaux@univ.mg', location: 'Dojo universitaire', meeting: 'Mardi & Jeudi 19h-21h', tags: ['Karaté', 'Judo', 'Taekwondo'], activities: ['Entraînements', 'Compétitions', 'Stages'], open: true, logo: null, responsible_id: 6 },
   { id: 'club-004', name: 'Club Environnement & Écologie', domain: 'environnement', icon: 'CE', color: '#c2ff01', description: 'Sensibilisation au développement durable', longDescription: 'Engagé pour la transition écologique de notre campus, le Club Environnement organise des actions concrètes.', members: 68, events: 15, founded: 2020, president: 'Naivo RAZANAMAHASOA', email: 'ecolo@univ.mg', location: 'Bâtiment A', meeting: 'Vendredi 16h-18h', tags: ['Écologie', 'Durabilité', 'Climat'], activities: ['Nettoyages', 'Plantations', 'Conférences'], open: true, logo: null, responsible_id: 7 },
   { id: 'club-005', name: 'Club Robotique', domain: 'tech', icon: 'CR', color: '#ff4502', description: 'Conception et programmation de robots', longDescription: 'Le Club Robotique est l\'espace idéal pour tous les passionnés de mécatronique, d\'électronique et d\'IA embarquée.', members: 36, events: 12, founded: 2019, president: 'Tiana RALAIVAO', email: 'robotique@univ.mg', location: 'Laboratoire FabLab', meeting: 'Lundi 17h-20h', tags: ['Robotique', 'Arduino', 'IA'], activities: ['Construction de robots', 'Compétitions'], open: true, logo: null, responsible_id: 8 },
   { id: 'club-006', name: 'Club Théâtre & Impro', domain: 'culture', icon: 'CT', color: '#dcb8ff', description: 'Expression scénique et improvisation', longDescription: 'Le Club Théâtre & Impro accueille les étudiants qui souhaitent s\'exprimer sur scène.', members: 29, events: 8, founded: 2016, president: 'Lova RAHARIMANANA', email: 'theatre@univ.mg', location: 'Théâtre universitaire', meeting: 'Mercredi 19h-22h', tags: ['Théâtre', 'Improvisation', 'Acting'], activities: ['Ateliers d\'impro', 'Pièces de théâtre'], open: true, logo: null, responsible_id: 9 },
   { id: 'club-007', name: 'Club Entrepreneuriat & Innovation', domain: 'entrepreneuriat', icon: 'CE', color: '#0044ff', description: 'Accompagnement des projets entrepreneuriaux', longDescription: 'Le Club Entrepreneuriat est un véritable incubateur étudiant.', members: 73, events: 21, founded: 2018, president: 'Tojo ANDRIAMANANTENA', email: 'entrepreneur@univ.mg', location: 'Maison de l\'entrepreneuriat', meeting: 'Jeudi 18h-20h', tags: ['Startup', 'Pitch', 'Business'], activities: ['Sessions pitch', 'Mentorat', 'Networking'], open: true, logo: null, responsible_id: 10 },
   { id: 'club-008', name: 'Club Photo & Vidéo', domain: 'culture', icon: 'CP', color: '#dcb8ff', description: 'Techniques de prise de vue, post-production', longDescription: 'Le Club Photo & Vidéo réunit les passionnés d\'image.', members: 48, events: 14, founded: 2019, president: 'Hery RASOLOFOSON', email: 'photo@univ.mg', location: 'Studio photo', meeting: 'Mardi 18h-21h', tags: ['Photographie', 'Vidéo', 'Photoshop'], activities: ['Ateliers photo', 'Expositions'], open: true, logo: null, responsible_id: 11 },
   { id: 'club-009', name: 'Club Musique & Chorale', domain: 'culture', icon: 'CM', color: '#dcb8ff', description: 'Pratique musicale collective', longDescription: 'Le Club Musique rassemble les musiciens amateurs et confirmés.', members: 35, events: 11, founded: 2017, president: 'Voahangy RALAIMIHOATRA', email: 'musique@univ.mg', location: 'Salle de musique', meeting: 'Lundi & Vendredi 18h-20h', tags: ['Musique', 'Chorale', 'MAO'], activities: ['Répétitions', 'Concerts'], open: true, logo: null, responsible_id: 12 },
   { id: 'club-010', name: 'Club Foot & Basketball', domain: 'sport', icon: 'FB', color: '#c2ff01', description: 'Entraînements et tournois', longDescription: 'Le Club Foot & Basketball propose des entraînements réguliers.', members: 92, events: 28, founded: 2015, president: 'Faly RAZANAKOTO', email: 'sport@univ.mg', location: 'Gymnase', meeting: 'Lundi, Mercredi, Vendredi 18h-20h', tags: ['Football', 'Basketball', 'Tournoi'], activities: ['Entraînements', 'Matchs', 'Tournois'], open: true, logo: null, responsible_id: 13 },
   { id: 'club-011', name: 'Club Bénévolat & Solidarité', domain: 'benevolat', icon: 'BS', color: '#0044ff', description: 'Actions solidaires et soutien scolaire', longDescription: 'Le Club Bénévolat & Solidarité organise des actions régulières.', members: 64, events: 22, founded: 2016, president: 'Mihary ANDRIANJATOVO', email: 'benevolat@univ.mg', location: 'Bâtiment B', meeting: 'Samedi 9h-12h', tags: ['Solidarité', 'Bénévolat'], activities: ['Soutien scolaire', 'Collectes', 'Visites'], open: true, logo: null, responsible_id: 14 },
   { id: 'club-012', name: 'Club Langues & Cultures', domain: 'langues', icon: 'LC', color: '#ff4502', description: 'Ateliers de conversation et échanges culturels', longDescription: 'Le Club Langues & Cultures favorise l\'ouverture internationale.', members: 81, events: 19, founded: 2018, president: 'Aina RAZAFINDRATANDRA', email: 'langues@univ.mg', location: 'Salle internationale', meeting: 'Mercredi 17h-19h', tags: ['Anglais', 'Espagnol', 'FLE'], activities: ['Ateliers langues', 'Soirées culturelles'], open: true, logo: null, responsible_id: 15 },
 ];

const EVENTS = [
  { id: 'evt-001', title: 'Atelier Python pour débutants', clubId: 'club-001', date: '2026-07-22', time: '18:00', duration: 120, location: 'Salle C-204', type: 'Atelier', description: 'Initiation à Python : variables, boucles, fonctions.', attendees: 24, maxAttendees: 30 },
  { id: 'evt-002', title: 'Hackathon IA & Éducation', clubId: 'club-001', date: '2026-07-28', time: '09:00', duration: 720, location: 'FabLab', type: 'Hackathon', description: '48h pour prototyper une solution IA.', attendees: 56, maxAttendees: 80 },
  { id: 'evt-003', title: 'Débat : IA et éthique', clubId: 'club-002', date: '2026-07-23', time: '14:00', duration: 180, location: 'Amphi B', type: 'Débat', description: 'L\'intelligence artificielle menace-t-elle nos libertés ?', attendees: 87, maxAttendees: 150 },
  { id: 'evt-004', title: 'Entraînement karaté', clubId: 'club-003', date: '2026-07-21', time: '19:00', duration: 120, location: 'Dojo', type: 'Entraînement', description: 'Séance kata et kumite, tous niveaux.', attendees: 18, maxAttendees: 25 },
  { id: 'evt-005', title: 'Compétition inter-universitaire', clubId: 'club-003', date: '2026-07-30', time: '10:00', duration: 480, location: 'Dojo', type: 'Compétition', description: 'Championnat régional d\'arts martiaux.', attendees: 32, maxAttendees: 50 },
  { id: 'evt-006', title: 'Journée nettoyage campus', clubId: 'club-004', date: '2026-07-26', time: '08:00', duration: 300, location: 'Entrée principale', type: 'Action', description: 'Grande opération de nettoyage.', attendees: 45, maxAttendees: 100 },
  { id: 'evt-007', title: 'Soirée impro : Freestyle', clubId: 'club-006', date: '2026-07-25', time: '20:00', duration: 150, location: 'Théâtre', type: 'Spectacle', description: 'Match d\'improvisation avec le Tana Comedy Club.', attendees: 62, maxAttendees: 120 },
  { id: 'evt-008', title: 'Pitch Night #5', clubId: 'club-007', date: '2026-07-29', time: '18:30', duration: 180, location: 'Maison entrepreneur', type: 'Networking', description: 'Les startups pitchent devant un jury.', attendees: 78, maxAttendees: 100 },
  { id: 'evt-009', title: 'Concert chorale été', clubId: 'club-009', date: '2026-07-27', time: '19:00', duration: 120, location: 'Cour du château', type: 'Concert', description: 'Concert en plein air du chœur universitaire.', attendees: 0, maxAttendees: 200 },
  { id: 'evt-010', title: 'Tournoi de foot 3v3', clubId: 'club-010', date: '2026-08-02', time: '14:00', duration: 360, location: 'Terrain synthétique', type: 'Tournoi', description: 'Tournoi estival de football à 3 contre 3.', attendees: 36, maxAttendees: 64 },
  { id: 'evt-011', title: 'Soirée culture japonaise', clubId: 'club-012', date: '2026-08-05', time: '18:00', duration: 180, location: 'Salle internationale', type: 'Culturel', description: 'Découverte de la culture japonaise.', attendees: 28, maxAttendees: 60 },
];

const DEMO_ACCOUNTS = [
   { label: 'Étudiant', name: 'Mika ANDRIAMATOA', role: 'student', matricule: '2023A0025', avatar: 'MA', redirect: 'dashboard.html' },
   { label: 'Responsable', name: 'Sarah ANDRIANARIVO', role: 'manager', matricule: '2022A0042', avatar: 'SA', redirect: 'manager.html' },
   { label: 'Admin', name: 'Hery RAKOTONIAINA', role: 'admin', matricule: '2018A0001', avatar: 'HR', redirect: 'admin.html' },
 ];

 const RESPONSIBLES = [
   { matricule: 'R01', name: 'Andry RAZAFY', email: 'andry@clubinfo.mg', role: 'student', club: 'Club Informatique', club_id: 1, avatar: 'AR' },
   { matricule: 'R02', name: 'Sarah RAKOTONIAINA', email: 'sarah@clubdebat.mg', role: 'student', club: 'Club Débat & Éloquence', club_id: 2, avatar: 'SR' },
   { matricule: 'R03', name: 'Toky RASOANAIVO', email: 'toky@clubmartiaux.mg', role: 'student', club: 'Club Arts Martiaux', club_id: 3, avatar: 'TR' },
   { matricule: 'R04', name: 'Antsa RAZAFY', email: 'antsa@clubeco.mg', role: 'student', club: 'Club Environnement & Écologie', club_id: 4, avatar: 'AN' },
   { matricule: 'R05', name: 'Naina ANDRIA', email: 'naina@clubrobot.mg', role: 'student', club: 'Club Robotique', club_id: 5, avatar: 'NA' },
   { matricule: 'R06', name: 'Voahangy RALA', email: 'voahangy@clubtheatre.mg', role: 'student', club: 'Club Théâtre & Impro', club_id: 6, avatar: 'VR' },
   { matricule: 'R07', name: 'Fanja RAKOTO', email: 'fanja@clubentrep.mg', role: 'student', club: 'Club Entrepreneuriat & Innovation', club_id: 7, avatar: 'FR' },
   { matricule: 'R08', name: 'Tiana RABE', email: 'tiana@clubphoto.mg', role: 'student', club: 'Club Photo & Vidéo', club_id: 8, avatar: 'TB' },
   { matricule: 'R09', name: 'Lova ANDRY', email: 'lova@clubmusique.mg', role: 'student', club: 'Club Musique & Chorale', club_id: 9, avatar: 'LA' },
   { matricule: 'R10', name: 'Mamy RAJAO', email: 'mamy@clubsport.mg', role: 'student', club: 'Club Foot & Basketball', club_id: 10, avatar: 'MR' },
   { matricule: 'R11', name: 'Mihary RANAIVO', email: 'mihary@clubbenev.mg', role: 'student', club: 'Club Bénévolat & Solidarité', club_id: 11, avatar: 'MH' },
   { matricule: 'R12', name: 'Niry RAZANA', email: 'niry@clublangues.mg', role: 'student', club: 'Club Langues & Cultures', club_id: 12, avatar: 'NR' },
 ];

const PERSONAS = [
  { name: 'Étudiant L1', role: 'Lycéen néo-bachelier', icon: '◈', color: 'orange', quote: 'Je veux m\'inscrire sans me déplacer, depuis mon téléphone.', needs: ['Inscription mobile', 'Découverte des clubs', 'Information centralisée'] },
  { name: 'Étudiant L3', role: 'Actif multi-clubs', icon: '◇', color: 'mauve', quote: 'Un espace perso pour voir mes inscriptions et participations.', needs: ['Espace personnel', 'Historique', 'Vue multi-clubs'] },
  { name: 'Internationale', role: 'Erasmus', icon: '◯', color: 'blue', quote: 'Une présentation claire des clubs, avec photos et thèmes.', needs: ['Multilingue', 'Annuaire visuel', 'Parrainage'] },
  { name: 'Responsable', role: 'Bureau club', icon: '◆', color: 'lime', quote: 'Gérer les membres et exporter les données simplement.', needs: ['Gestion membres', 'Suivi présences', 'Exports'] },
];

const STORAGE = {
  get(key, fallback = null) {
    try { const v = localStorage.getItem('clubhub_' + key); return v ? JSON.parse(v) : fallback; } catch (e) { return fallback; }
  },
  set(key, value) { try { localStorage.setItem('clubhub_' + key, JSON.stringify(value)); return true; } catch (e) { return false; } },
  remove(key) { localStorage.removeItem('clubhub_' + key); }
};

const ICONS = {
  search: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>',
  bell: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>',
  settings: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 01-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>',
  user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
  users: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
  calendar: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
  chart: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
  logout: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
  menu: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
  x: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
  check: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>',
  plus: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
  arrow: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
  arrowUp: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>',
  arrowRight: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
  mail: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
  lock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
  map: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
  clock: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  download: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
  upload: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>',
  edit: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>',
  trash: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>',
  list: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
  grid: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
  alert: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
  shield: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
  zap: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
  home: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  refresh: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>',
  layers: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
  award: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
  eye: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
  sparkles: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3l1.9 5.8L20 11l-5.8 1.9L12 19l-2.2-6.1L4 11l6.1-2.2L12 3z"/></svg>',
  folder: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>',
};

// Notifications seedees (utilisees par NotifBadge + notifications.html)
const NOTIFICATIONS = [
  { id: 1, user_matricule: 'MA', kind: 'inscription', icon: '✓', title: 'Inscription confirmee',   message: 'Vous etes maintenant membre du Club Robotique.',  link: 'club.html?id=5',   read: false, created_at: '2026-07-26T11:30:00' },
  { id: 2, user_matricule: 'MA', kind: 'event',      icon: '◷', title: 'Nouvel evenement',         message: 'Atelier Python pour debutants publie.',          link: 'events.html?id=2', read: false, created_at: '2026-07-26T10:00:00' },
  { id: 3, user_matricule: 'MA', kind: 'event',      icon: '!', title: 'Rappel : Debat IA',        message: 'Demain a 14h en Amphi B.',                       link: 'events.html?id=5', read: false, created_at: '2026-07-26T08:00:00' },
  { id: 4, user_matricule: 'MA', kind: 'club',       icon: 'i', title: 'Nouveau club',             message: 'Club Langues & Cultures ouvert.',                link: 'club.html?id=12',  read: true,  created_at: '2026-07-25T15:00:00' },
  { id: 5, user_matricule: 'MA', kind: 'system',     icon: '★', title: '+50 points gagnes',         message: 'Pour ta participation au debat.',                link: 'profile.html',     read: true,  created_at: '2026-07-24T12:00:00' },
  { id: 6, user_matricule: 'MA', kind: 'inscription', icon: '✓', title: 'Bienvenue au Club Debat',   message: 'Tu peux maintenant acceder aux events.',         link: 'club.html?id=2',   read: true,  created_at: '2026-07-20T09:00:00' },
  { id: 7, user_matricule: 'SA', kind: 'event',      icon: '◷', title: 'Nouvel inscrit',            message: 'Mika ANDRIAMATOA s\'est inscrit a ton Atelier.', link: 'manager.html',     read: false, created_at: '2026-07-26T11:45:00' },
  { id: 8, user_matricule: 'SA', kind: 'system',     icon: '★', title: 'QR scanne 14 fois',         message: 'Atelier du 25/07 - 14 presents valides.',        link: 'manager.html?tab=attendance', read: false, created_at: '2026-07-26T12:00:00' },
  { id: 9, user_matricule: 'HR', kind: 'system',     icon: '!', title: 'Pic d\'inscriptions',        message: '47 nouvelles inscriptions aujourd\'hui.',         link: 'admin.html',       read: false, created_at: '2026-07-26T11:00:00' },
];

const USERS = [
   { matricule:'MA', name:'Mika ANDRIAMATOA', filiere:'L1 Info', points: 120, avatar:'MA', id:1, role:'student' },
   { matricule:'SA', name:'Sarah ANDRIANARIVO', filiere:'L3 Info', points: 285, avatar:'SA', id:2, role:'manager' },
   { matricule:'HR', name:'Hery RAKOTONIAINA', filiere:'Admin', points: 95, avatar:'HR', id:3, role:'admin' },
   { matricule:'TR', name:'Toky RASOANAIVO', filiere:'L2 STAPS', points: 198, avatar:'TR', id:4, role:'student' },
   { matricule:'AR', name:'Antsa RAZAFY', filiere:'L2 SVT', points: 174, avatar:'AR', id:5, role:'student' },
   { matricule:'NA', name:'Naina ANDRIA', filiere:'M1 Info', points: 312, avatar:'NA', id:6, role:'student' },
   { matricule:'VR', name:'Voahangy RALA', filiere:'L3 Lettres', points: 156, avatar:'VR', id:7, role:'student' },
   { matricule:'FR', name:'Fanja RAKOTO', filiere:'M2 Eco', points: 245, avatar:'FR', id:8, role:'student' },
   { matricule:'TR2', name:'Tiana RABE', filiere:'L2 Info', points: 188, avatar:'TR', id:9, role:'student' },
   { matricule:'LA', name:'Lova ANDRY', filiere:'L3 Musique', points: 222, avatar:'LA', id:10, role:'student' },
   { matricule:'MR', name:'Mamy RAJAO', filiere:'L1 STAPS', points: 134, avatar:'MR', id:11, role:'student' },
   { matricule:'MR2', name:'Mihary RANAIVO', filiere:'L2 Droit', points: 167, avatar:'MR', id:12, role:'student' },
   { matricule:'NR', name:'Niry RAZANA', filiere:'L1 LEA', points: 89, avatar:'NR', id:13, role:'student' },
   { matricule:'FB', name:'Faly BEZANDRY', filiere:'M1 Info', points: 256, avatar:'FB', id:14, role:'student' },
   { matricule:'R01', name:'Andry RAZAFY', filiere:'L3 Info', points: 0, avatar:'AR', id:15, role:'student' },
   { matricule:'R02', name:'Sarah RAKOTONIAINA', filiere:'L2 Info', points: 0, avatar:'SR', id:16, role:'student' },
   { matricule:'R03', name:'Toky RASOANAIVO', filiere:'L2 STAPS', points: 0, avatar:'TR', id:17, role:'student' },
   { matricule:'R04', name:'Antsa RAZAFY', filiere:'L2 SVT', points: 0, avatar:'AN', id:18, role:'student' },
   { matricule:'R05', name:'Naina ANDRIA', filiere:'M1 Info', points: 0, avatar:'NA', id:19, role:'student' },
   { matricule:'R06', name:'Voahangy RALA', filiere:'L3 Lettres', points: 0, avatar:'VR', id:20, role:'student' },
   { matricule:'R07', name:'Fanja RAKOTO', filiere:'M2 Eco', points: 0, avatar:'FR', id:21, role:'student' },
   { matricule:'R08', name:'Tiana RABE', filiere:'L2 Info', points: 0, avatar:'TB', id:22, role:'student' },
   { matricule:'R09', name:'Lova ANDRY', filiere:'L3 Musique', points: 0, avatar:'LA', id:23, role:'student' },
   { matricule:'R10', name:'Mamy RAJAO', filiere:'L1 STAPS', points: 0, avatar:'MR', id:24, role:'student' },
   { matricule:'R11', name:'Mihary RANAIVO', filiere:'L2 Droit', points: 0, avatar:'MH', id:25, role:'student' },
   { matricule:'R12', name:'Niry RAZANA', filiere:'L1 LEA', points: 0, avatar:'NR', id:26, role:'student' },
 ];

if (typeof window !== 'undefined') {
  window.CLUBHUB = window.ClubHub = { CLUB_CATEGORIES, CLUBS, EVENTS, DEMO_ACCOUNTS, PERSONAS, STORAGE, ICONS, NOTIFICATIONS, USERS };
}
