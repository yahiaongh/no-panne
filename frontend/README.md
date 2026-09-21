# No Panne — React Native / Expo Frontend

Part of the monorepo [`no-panne`](../README.md). To run:

```bash
cd frontend   # from the repo root
npm install
npx expo start
```

On-demand roadside assistance & mobile car repair app for the Algerian market.
Dark slate theme with an amber/orange "automotive warning" accent, bilingual
FR / AR (with RTL layout mirroring), built as a single Expo app with
**state-driven navigation** (no react-navigation dependency needed for this
prototype — a lightweight `view` state switch in `AppNavigator`).

## Run it

```bash
npm install
npx expo start
```

Scan the QR code with Expo Go (iOS/Android), or press `i` / `a` for a
simulator, or `w` for a web preview.

> Note: the original brief described wrapping the UI in a static iPhone
> "device frame" (`max-w-md`, `rounded-[44px]`, etc.) — that's a web-canvas
> convention for mocking up a phone inside a browser. Here the app *is* the
> real thing running on-device (or in a simulator), so there's no frame to
> render: your phone/simulator is the frame. If you also want a browser-based
> clickable mockup for stakeholders, run `npx expo start --web` and place the
> browser window inside any device-mockup screenshot tool.

## File structure

```
no-panne/
├── App.js                        # Entry point — wraps AppProvider + AppNavigator
├── app.json                      # Expo config (name, bundle ids, dark UI style)
├── babel.config.js
├── package.json
└── src/
    ├── context/
    │   └── AppContext.js         # lang state ('fr' | 'ar'), isRTL flag, t() translator
    ├── i18n/
    │   └── translations.js       # Full FR + AR string dictionaries
    ├── theme/
    │   └── theme.js              # Colors, spacing, radii, type scale, shadows
    ├── data/
    │   └── mockData.js           # Services, nearby mechanics, issue tags,
    │                              # active provider, mission statuses, review tags
    ├── components/
    │   ├── Button.js             # primary / ghost / danger / dark variants
    │   ├── Primitives.js         # Card, Badge, SectionTitle
    │   ├── TopBar.js             # Language toggle + manual view switcher (testing)
    │   ├── BottomNav.js          # Accueil / Mes Demandes / Favoris / Compte
    │   └── Timeline.js           # Horizontal mission-status progression
    └── screens/
        ├── HomeScreen.js         # View 1 — client hub (SOS hero, service grid,
        │                          # nearby providers list, bottom nav)
        ├── EmergencyRequestScreen.js  # View 2 — 3-step flow (issue → location → confirm)
        ├── TrackingScreen.js     # View 3 — radar search animation → provider
        │                          # card, timeline, call button, payment pill
        └── ReviewModal.js        # View 4 — star rating, tag badges, cash receipt
```

## Views implemented

1. **Home (Client Hub)** — greeting header, notification bell with badge,
   pulsating SOS emergency card, 6-item quick-service grid, nearby mobile
   mechanics list (distance / rating / vehicle / availability), bottom nav.
2. **Emergency Request Flow** — step 1 issue picker (5 visual tags), step 2
   location confirmation with a stylized mock map (grid + GPS pin + notes
   input), step 3 summary + "Lancer la recherche" (10 km radius) button.
3. **Live Tracking & Mission Status** — animated radar sweep while
   "searching", then a provider card (photo initials, rating, plate, call
   button), a 4-stage horizontal timeline (Acceptée → En route → Sur place →
   Terminée) with an ETA pill, a cash-only payment reminder pill, and a demo
   button to step the mission forward.
4. **Post-Service Review Modal** — 1–5 tappable stars, 4 quick tag badges,
   a cash-amount receipt field + confirmation checkbox, submit → thank-you
   state → returns to Home.

## Notes on implementation choices

- **State-driven, not stack navigation**: `AppNavigator` holds a single
  `view` string (`home | emergency | tracking`) plus a `reviewVisible`
  boolean for the modal. The `TopBar` exposes a segmented control to jump
  between views manually for QA/demo purposes, exactly as specified.
- **RTL**: switching to `العربية` flips `isRTL` in `AppContext`. Rather than
  calling `I18nManager.forceRTL` (which requires an app reload on real
  devices), each screen mirrors its own `flexDirection` and `textAlign`
  per-component — this keeps the toggle instant and safe for a live demo.
  For production you'd likely also call `I18nManager.forceRTL(true)` behind
  a restart prompt so native RTL (scroll direction, gesture edges, etc.) is
  fully correct.
- **Animations** use the core `Animated` API only (radar sweep/ping, SOS
  card glow pulse) — no extra animation library needed to keep the prototype
  dependency-light.
- **Icons** come from `@expo/vector-icons` (bundled with Expo, no extra
  install).
- All data in `src/data/mockData.js` is mock/demo data — no backend calls.
