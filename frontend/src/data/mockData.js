// All mock/demo data for the No Panne prototype lives here.

export const SERVICES = [
  { id: "depannage", icon: "construct", labelKey: "serviceDepannage" },
  { id: "crevaison", icon: "disc", labelKey: "serviceCrevaison" },
  { id: "remorquage", icon: "car-sport", labelKey: "serviceRemorquage" },
  { id: "vidange", icon: "water", labelKey: "serviceVidange" },
  { id: "diagnostic", icon: "hardware-chip", labelKey: "serviceDiagnostic" },
  { id: "lavage", icon: "sparkles", labelKey: "serviceLavage" },
];

export const NEARBY_PROVIDERS = [
  {
    id: "p1",
    name: "Rachid M.",
    distanceKm: 1.8,
    rating: 4.9,
    reviews: 124,
    vehicle: "Fourgon d'intervention",
    available: true,
    photo: "RM",
  },
  {
    id: "p2",
    name: "Yacine B.",
    distanceKm: 2.4,
    rating: 4.7,
    reviews: 88,
    vehicle: "Camionnette",
    available: true,
    photo: "YB",
  },
  {
    id: "p3",
    name: "Amine T.",
    distanceKm: 3.1,
    rating: 4.6,
    reviews: 51,
    vehicle: "Fourgon d'intervention",
    available: false,
    photo: "AT",
  },
];

export const ISSUE_TAGS = [
  { id: "flat", icon: "disc-outline", labelKey: "issueFlatTire" },
  { id: "battery", icon: "battery-dead-outline", labelKey: "issueBattery" },
  { id: "engine", icon: "warning-outline", labelKey: "issueEngine" },
  { id: "overheat", icon: "thermometer-outline", labelKey: "issueOverheat" },
  { id: "accident", icon: "alert-circle-outline", labelKey: "issueAccident" },
];

export const ACTIVE_PROVIDER = {
  name: "Rachid M.",
  rating: 4.9,
  reviews: 124,
  plate: "16-118-024",
  vehicle: "Fourgon d'intervention",
  photo: "RM",
  phone: "+213 555 12 34 56",
};

export const MISSION_STATUSES = [
  "statusAccepted",
  "statusEnRoute",
  "statusOnSite",
  "statusDone",
];

export const REVIEW_TAGS = [
  "tagPunctual",
  "tagFairPrice",
  "tagProfessional",
  "tagResolved",
];
