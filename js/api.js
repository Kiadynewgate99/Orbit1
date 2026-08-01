/* ============================================================
 * ClubHub v1.0.0 - API client
 * Wrapper qui utilise l'API reelle si dispo, sinon fallback
 * sur les donnees statiques de data.js.
 *
 * Activation du mode API :
 *   localStorage.setItem('clubhub_api_mode', 'live')
 *   OU  ?api=live dans l'URL
 * Desactivation :
 *   localStorage.setItem('clubhub_api_mode', 'off')
 * ============================================================ */

(function () {
  'use strict';

  const useApi = () => {
    const url = new URL(window.location.href);
    if (url.searchParams.get('api') === 'live')  return true;
    if (url.searchParams.get('api') === 'off')   return false;
    return localStorage.getItem('clubhub_api_mode') === 'live';
  };

  const base = () => {
    // si on est sur http://localhost/clubhub-v2/ alors l'API est sur /clubhub-v2/api
    // sinon on tente /api relatif
    return (window.CLUBHUB_API_BASE || '/api') + '/';
  };

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
      // mode mock : on delegue a un faux fetch qui repond depuis CLUBHUB
      return mockFetch(method, path, body);
    }
    const r = await fetch(url, opts);
    if (!r.ok) {
      const j = await r.json().catch(() => ({}));
      throw new Error(j.error || ('HTTP ' + r.status));
    }
    return r.json();
  }

  // -------- MOCK FETCH (fallback) -----------------------------
  async function mockFetch(method, path, body) {
    // simple simu - renvoie depuis CLUBHUB
    const ok = (data) => ({ ok: true, data });
    await new Promise(r => setTimeout(r, 80));
    if (path === 'clubs')          return ok(window.CLUBHUB.CLUBS);
    if (path === 'events')         return ok(window.CLUBHUB.EVENTS);
    if (path === 'notifications')  return ok([]);
    if (path === 'stats')          return ok({
      clubs_total: 12, users_total: 847, events_upcoming: 11,
      inscriptions_total: 709, top_clubs: [],
    });
    if (path === 'me')             return { ok: true, data: JSON.parse(localStorage.getItem('clubhub_current_user') || 'null') };
    if (path === 'login') {
      // simule connexion : on regarde dans DEMO_ACCOUNTS
      const a = (window.CLUBHUB.DEMO_ACCOUNTS || []).find(a => a.matricule === body.matricule);
      if (!a) throw new Error('Identifiants invalides');
      const u = { ...a };
      delete u.password;
      localStorage.setItem('clubhub_current_user', JSON.stringify(u));
      return ok({ token: 'mock', user: u });
    }
    return ok([]);
  }

  // -------- API publique --------------------------------------
  window.ClubHubAPI = {
    isLive: useApi,
    setMode: (mode) => { localStorage.setItem('clubhub_api_mode', mode); location.reload(); },

    login:    (matricule, password)            => request('POST', 'login', { matricule, password }),
    logout:   ()                                => request('POST', 'logout'),
    me:       ()                                => request('GET', 'me'),

    clubs:    (params = {})                     => {
      const qs = new URLSearchParams(params).toString();
      return request('GET', 'clubs' + (qs ? '?' + qs : ''));
    },
    club:     (id)                              => request('GET', 'clubs?id=' + id),
    createClub:  (data)                         => request('POST', 'clubs', data),
    updateClub:  (id, data)                     => request('PUT', 'clubs?id=' + id, data),
    deleteClub:  (id)                           => request('DELETE', 'clubs?id=' + id),

    events:   (params = {})                     => {
      const qs = new URLSearchParams(params).toString();
      return request('GET', 'events' + (qs ? '?' + qs : ''));
    },
    createEvent: (data)                         => request('POST', 'events', data),
    deleteEvent: (id)                           => request('DELETE', 'events?id=' + id),

    myClubs:    ()                              => request('GET', 'inscriptions'),
    joinClub:   (club_id)                       => request('POST', 'inscriptions', { club_id }),
    leaveClub:  (club_id)                       => request('DELETE', 'inscriptions', { club_id }),

    notifications:    ()                        => request('GET', 'notifications'),
    markAllRead:      ()                        => request('POST', 'notifications?read_all=1'),

    stats:    ()                                => request('GET', 'stats'),
  };
})();
