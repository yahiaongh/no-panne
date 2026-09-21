// Design tokens for No Panne.
// Palette: near-black slate base with an amber/orange automotive-warning accent.
// The accent is used sparingly (SOS + primary CTAs) so it keeps its urgency.

export const colors = {
  bgDeep: "#020617", // slate-950 — app background
  bgSurface: "#0B1220", // between slate-950/900 — screen surface
  bgCard: "#111C2E", // elevated card
  bgCardAlt: "#0F1A2A",
  border: "#1E293B", // slate-800
  borderMuted: "#152036",

  textPrimary: "#F8FAFC",
  textSecondary: "#CBD5E1",
  textMuted: "#7C8AA3",
  textFaint: "#4B5872",

  amber: "#F59E0B",
  amberDeep: "#D97706",
  orange: "#EA580C",
  orangeDeep: "#C2410C",

  danger: "#DC2626",
  dangerDeep: "#991B1B",
  success: "#22C55E",
  successBg: "#132A1C",

  overlay: "rgba(2, 6, 23, 0.82)",
};

export const spacing = {
  xs: 4,
  sm: 8,
  md: 12,
  lg: 16,
  xl: 20,
  xxl: 28,
  xxxl: 36,
};

export const radii = {
  sm: 10,
  md: 14,
  lg: 20,
  xl: 28,
  pill: 999,
};

export const type = {
  display: { fontSize: 26, fontWeight: "700", letterSpacing: -0.3 },
  h1: { fontSize: 20, fontWeight: "700", letterSpacing: -0.2 },
  h2: { fontSize: 17, fontWeight: "600" },
  body: { fontSize: 14, fontWeight: "400" },
  bodyStrong: { fontSize: 14, fontWeight: "600" },
  small: { fontSize: 12, fontWeight: "500" },
  tiny: { fontSize: 11, fontWeight: "500" },
};

export const shadow = {
  card: {
    shadowColor: "#000",
    shadowOffset: { width: 0, height: 8 },
    shadowOpacity: 0.35,
    shadowRadius: 16,
    elevation: 6,
  },
  glow: {
    shadowColor: "#F59E0B",
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.45,
    shadowRadius: 18,
    elevation: 10,
  },
};
