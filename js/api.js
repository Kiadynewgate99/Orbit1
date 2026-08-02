/* ============================================================
 * ClubHub v1.0.0 - API client
 * Wrapper qui utilise l'API reelle si dispo, sinon fallback
 * sur les donnees statiques de data.js.
 *
 * Mode LIVE par defaut (branche sur le backend PHP).
 * Pour forcer le mode mock (dev sans backend) :
 *   localStorage.setItem('clubhub_api_mode', 'off')
 *   OU  ?api=off dans l'URL
 * ============================================================ */

(function () {
  'use strict';

  const useApi = () => {
    const url = new URL(window.location.href);
    if (url.searchParams.get('api') === 'live')  return true;
    if (url.searchParams.get('api') === 'off')   return false;
    // live par defaut : seul un choix explicite "off" bascule en mock
    return localStorage.getItem('clubhub_api_mode') !== 'off';
  };

  const base = () => '';

  const token = () => localStorage.getItem('clubhub_token') || '';

  const headers = () => {
    const h = { 'Content-Type': 'application/json' };
    if (token()) h['Authorization'] = 'Bearer ' + token();
    return h;
  };

  async function request(method, path, body) {
    const url = (useApi() ? base() : '') + path;
    const opts = {
      method,
      headers: useApi() ? headers() : { 'Content-Type': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);
    if (!useApi()) {
      // mode mock explicite (?api=off) : on delegue a un faux fetch qui repond depuis CLUBHUB
      return mockFetch(method, path, body);
    }
    const r = await fetch(url, opts);
    if (!r.ok) {
      const j = await r.json().catch(() => ({}));
      throw new Error(j.error || ('HTTP ' + r.status));
    }
    return r.json();
  }

  // -------- MOCK FETCH (fallback dev uniquement, ?api=off) -----
  async function mockFetch(method, path, body) {
    // simple simu - renvoie depuis CLUBHUB
    const ok = (data) => ({ ok: true, data });
    await new Promise(r => setTimeout(r, 80));
    if (path.startsWith('clubs'))          return ok({ data: window.CLUBHUB.CLUBS, total: window.CLUBHUB.CLUBS.length, page: 1, per_page: 20 });
    if (path.startsWith('events'))         return ok({ data: window.CLUBHUB.EVENTS, total: window.CLUBHUB.EVENTS.length, page: 1, per_page: 20 });
    if (path === 'notifications.php')  return ok({ data: window.CLUBHUB.NOTIFICATIONS || [], total: (window.CLUBHUB.NOTIFICATIONS || []).length, page: 1, per_page: 20 });
    if (path === 'stats.php')          return ok({
      data: {
        clubs_total: window.CLUBHUB.CLUBS.length,
        users_total: (window.CLUBHUB.USERS || []).length,
        events_upcoming: window.CLUBHUB.EVENTS.length,
        inscriptions_total: 709,
        top_clubs: window.CLUBHUB.CLUBS.slice(0, 5),
        users_ranking: (window.CLUBHUB.USERS || []).sort((a, b) => b.points - a.points),
      },
      total: 1,
      page: 1,
      per_page: 20,
    });
    if (path === 'me.php')             return ok(JSON.parse(localStorage.getItem('clubhub_current_user') || 'null'));
    if (path.startsWith('search')) {
      const q = path.split('?q=')[1] || '';
      const clubs = window.CLUBHUB.CLUBS.filter(c => c.name.toLowerCase().includes(q.toLowerCase())).slice(0, 10);
      const events = window.CLUBHUB.EVENTS.filter(e => e.title.toLowerCase().includes(q.toLowerCase())).slice(0, 10);
      return ok({ clubs, events, q });
    }
    if (path === 'audit.php') {
      return ok([
        { id: 1, action: 'login', user: 'MA', timestamp: '2026-07-26T11:30:00', ip: '192.168.1.1' },
        { id: 2, action: 'join_club', user: 'MA', timestamp: '2026-07-26T11:35:00', ip: '192.168.1.1' },
        { id: 3, action: 'create_event', user: 'SA', timestamp: '2026-07-26T12:00:00', ip: '192.168.1.2' },
        { id: 4, action: 'login', user: 'HR', timestamp: '2026-07-26T12:15:00', ip: '192.168.1.3' },
        { id: 5, action: 'delete_club', user: 'HR', timestamp: '2026-07-26T13:00:00', ip: '192.168.1.3' },
      ]);
    }
    if (path === 'login.php') {
      throw new Error('Mode demo desactive : connectez-vous avec un vrai compte (backend requis).');
    }
    return ok([]);
  }

  // -------- API publique --------------------------------------
  window.ClubHubAPI = {
    isLive: useApi,
    setMode: (mode) => { localStorage.setItem('clubhub_api_mode', mode); location.reload(); },

    login:    (matricule, password)            => request('POST', 'login.php', { matricule, password }),
    register: (data)                           => request('POST', 'register.php', data),
    logout:   ()                                => request('POST', 'logout.php'),
    me:       ()                                => request('GET', 'me.php'),

    clubs:    (params = {})                     => {
      const qs = new URLSearchParams(params).toString();
      return request('GET', 'clubs.php' + (qs ? '?' + qs : ''));
    },
    club:     (id)                              => request('GET', 'clubs.php?id=' + id),
    createClub:  (data)                         => request('POST', 'clubs.php', data),
    updateClub:  (id, data)                     => request('PUT', 'clubs.php?id=' + id, data),
    deleteClub:  (id)                           => request('DELETE', 'clubs.php?id=' + id),

    events:   (params = {})                     => {
      const qs = new URLSearchParams(params).toString();
      return request('GET', 'events.php' + (qs ? '?' + qs : ''));
    },
    createEvent: (data)                         => request('POST', 'events.php', data),
    deleteEvent: (id)                           => request('DELETE', 'events.php?id=' + id),

    myClubs:    ()                              => request('GET', 'inscriptions.php'),
    joinClub:   (club_id)                       => request('POST', 'inscriptions.php', { club_id }),
    leaveClub:  (club_id)                       => request('DELETE', 'inscriptions.php', { club_id }),

    notifications:    ()                        => request('GET', 'notifications.php'),
    markAllRead:      ()                        => request('POST', 'notifications.php?read_all=1'),
     markRead:         (id)                      => request('POST', 'notifications.php?id=' + id + '&read=1'),

    stats:    ()                                => request('GET', 'stats.php'),

    search:   (q)                              => request('GET', 'search.php?q=' + encodeURIComponent(q)),
    audit:    ()                                => request('GET', 'audit.php'),

    users:    (params = {})                     => {
      const qs = new URLSearchParams(params).toString();
      return request('GET', 'stats.php' + (qs ? '?' + qs : ''));
    },
  };
})();