# No Panne

On-demand roadside assistance & mobile car repair app for the Algerian market.
Bilingual FR / AR (with RTL), dark slate theme with an amber/orange
"automotive warning" accent. Built as an Expo (React Native) app with
state-driven navigation.

> **Status:** frontend prototype with mock data. Backend scaffolding planned —
> reserved for the `backend/` directory.

## Repository layout

```
no-panne/
├── frontend/    # Expo / React Native mobile app (Android, iOS, web)
└── backend/     # (coming soon) API / server
```

## Frontend

See [`frontend/README.md`](frontend/README.md) for the full structure and
feature overview.

```bash
cd frontend
npm install
npx expo start
```

Scan the QR code with Expo Go (iOS/Android), press `i` / `a` for a simulator,
or `w` for a web preview.

> Note: this project targets **Expo SDK 51**. The current Play Store version of
> Expo Go only supports the latest SDK, so for running on a physical Android
> device, install the SDK 51 build of Expo Go (e.g. `Expo-Go-SDK51.apk`) and
> open the app via a tunnel or LAN URL.

## Environment variables

None required. All data is inline mock data (`frontend/src/data/mockData.js`).

## License

Private prototype.