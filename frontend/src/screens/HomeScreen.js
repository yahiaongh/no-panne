import React, { useEffect, useRef } from "react";
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  Animated,
  StyleSheet,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useApp } from "../context/AppContext";
import { colors, radii, spacing, type, shadow } from "../theme/theme";
import { Card, Badge, SectionTitle } from "../components/Primitives";
import BottomNav from "../components/BottomNav";
import { SERVICES, NEARBY_PROVIDERS } from "../data/mockData";

export default function HomeScreen({ onStartEmergency }) {
  const { t, isRTL } = useApp();
  const pulse = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const loop = Animated.loop(
      Animated.sequence([
        Animated.timing(pulse, { toValue: 1, duration: 1100, useNativeDriver: false }),
        Animated.timing(pulse, { toValue: 0, duration: 1100, useNativeDriver: false }),
      ])
    );
    loop.start();
    return () => loop.stop();
  }, [pulse]);

  const glowOpacity = pulse.interpolate({ inputRange: [0, 1], outputRange: [0.25, 0.6] });

  return (
    <View style={{ flex: 1 }}>
      <ScrollView
        style={{ flex: 1, backgroundColor: colors.bgDeep }}
        contentContainerStyle={{ padding: spacing.lg, paddingBottom: spacing.xxxl }}
        showsVerticalScrollIndicator={false}
      >
        {/* Header */}
        <View style={[styles.header, isRTL && { flexDirection: "row-reverse" }]}>
          <View style={isRTL ? { alignItems: "flex-end" } : null}>
            <Text style={styles.greeting}>{t("greeting")}</Text>
            <View style={[styles.locRow, isRTL && { flexDirection: "row-reverse" }]}>
              <Ionicons name="location" size={13} color={colors.amber} />
              <Text style={styles.locText}>{t("location")}</Text>
            </View>
          </View>
          <TouchableOpacity style={styles.bellWrap}>
            <Ionicons name="notifications-outline" size={20} color={colors.textPrimary} />
            <View style={styles.bellBadge}>
              <Text style={styles.bellBadgeText}>3</Text>
            </View>
          </TouchableOpacity>
        </View>

        {/* SOS Hero */}
        <Animated.View style={[styles.sosOuterGlow, { shadowOpacity: glowOpacity }]}>
          <View style={styles.sosCard}>
            <View style={[styles.sosTopRow, isRTL && { flexDirection: "row-reverse" }]}>
              <View style={styles.sosIconDot}>
                <Ionicons name="warning" size={16} color="#1C1300" />
              </View>
              <Text style={styles.sosKicker}>SOS</Text>
            </View>
            <Text style={[styles.sosTitle, isRTL && { textAlign: "right" }]}>
              {t("sosTitle")}
            </Text>
            <Text style={[styles.sosSubtitle, isRTL && { textAlign: "right" }]}>
              {t("sosSubtitle")}
            </Text>
            <TouchableOpacity
              style={[styles.sosButton, isRTL && { flexDirection: "row-reverse" }]}
              activeOpacity={0.88}
              onPress={onStartEmergency}
            >
              <Ionicons name="flash" size={17} color="#3A0A02" />
              <Text style={styles.sosButtonText}>{t("sosButton")}</Text>
            </TouchableOpacity>
          </View>
        </Animated.View>

        {/* Quick services */}
        <SectionTitle style={{ marginTop: spacing.xxl }}>
          {t("servicesTitle")}
        </SectionTitle>
        <View style={styles.grid}>
          {SERVICES.map((s) => (
            <TouchableOpacity key={s.id} style={styles.serviceCard} activeOpacity={0.8}>
              <View style={styles.serviceIconWrap}>
                <Ionicons name={s.icon} size={20} color={colors.amber} />
              </View>
              <Text style={styles.serviceLabel} numberOfLines={2}>
                {t(s.labelKey)}
              </Text>
            </TouchableOpacity>
          ))}
        </View>

        {/* Nearby providers */}
        <SectionTitle style={{ marginTop: spacing.xxl }}>
          {t("nearbyTitle")}
        </SectionTitle>
        <View style={{ gap: spacing.md }}>
          {NEARBY_PROVIDERS.map((p) => (
            <Card key={p.id} style={{ padding: spacing.md }}>
              <View style={[styles.provRow, isRTL && { flexDirection: "row-reverse" }]}>
                <View style={styles.avatar}>
                  <Text style={styles.avatarText}>{p.photo}</Text>
                </View>
                <View style={{ flex: 1, marginHorizontal: spacing.md }}>
                  <View
                    style={[
                      styles.provTopLine,
                      isRTL && { flexDirection: "row-reverse" },
                    ]}
                  >
                    <Text style={styles.provName}>{p.name}</Text>
                    <Badge
                      label={p.available ? t("available") : t("busy")}
                      tone={p.available ? "success" : "muted"}
                    />
                  </View>
                  <Text style={[styles.provMeta, isRTL && { textAlign: "right" }]}>
                    {p.vehicle}
                  </Text>
                  <View
                    style={[styles.provBottomLine, isRTL && { flexDirection: "row-reverse" }]}
                  >
                    <View style={[styles.inlineRow, isRTL && { flexDirection: "row-reverse" }]}>
                      <Ionicons name="star" size={12} color={colors.amber} />
                      <Text style={styles.provStat}>
                        {p.rating} ({p.reviews})
                      </Text>
                    </View>
                    <Text style={styles.provDistance}>{p.distanceKm} km</Text>
                  </View>
                </View>
              </View>
            </Card>
          ))}
        </View>
      </ScrollView>
      <BottomNav />
    </View>
  );
}

const styles = StyleSheet.create({
  header: {
    flexDirection: "row",
    alignItems: "flex-start",
    justifyContent: "space-between",
    marginBottom: spacing.lg,
  },
  greeting: { ...type.display, color: colors.textPrimary },
  locRow: { flexDirection: "row", alignItems: "center", gap: 4, marginTop: 4 },
  locText: { ...type.small, color: colors.textMuted },
  bellWrap: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: colors.bgCard,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
  },
  bellBadge: {
    position: "absolute",
    top: -3,
    right: -3,
    backgroundColor: colors.danger,
    borderRadius: 8,
    minWidth: 16,
    height: 16,
    alignItems: "center",
    justifyContent: "center",
    paddingHorizontal: 3,
  },
  bellBadgeText: { color: "#fff", fontSize: 9, fontWeight: "800" },

  sosOuterGlow: {
    borderRadius: radii.xl,
    shadowColor: colors.amber,
    shadowOffset: { width: 0, height: 8 },
    shadowRadius: 22,
  },
  sosCard: {
    borderRadius: radii.xl,
    padding: spacing.xl,
    backgroundColor: colors.dangerDeep,
    borderWidth: 1,
    borderColor: "#F97316",
    overflow: "hidden",
  },
  sosTopRow: { flexDirection: "row", alignItems: "center", gap: 8, marginBottom: spacing.md },
  sosIconDot: {
    width: 26,
    height: 26,
    borderRadius: 13,
    backgroundColor: colors.amber,
    alignItems: "center",
    justifyContent: "center",
  },
  sosKicker: { color: "#FED7AA", fontSize: 12, fontWeight: "800", letterSpacing: 1 },
  sosTitle: { color: "#fff", fontSize: 21, fontWeight: "800", lineHeight: 27 },
  sosSubtitle: { color: "#FFE4D6", fontSize: 13, marginTop: 6, marginBottom: spacing.lg },
  sosButton: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 8,
    backgroundColor: colors.amber,
    borderRadius: radii.pill,
    paddingVertical: 14,
  },
  sosButtonText: { color: "#3A0A02", fontWeight: "800", fontSize: 15 },

  grid: {
    flexDirection: "row",
    flexWrap: "wrap",
    gap: spacing.md,
    justifyContent: "space-between",
  },
  serviceCard: {
    width: "31%",
    backgroundColor: colors.bgCard,
    borderRadius: radii.md,
    borderWidth: 1,
    borderColor: colors.border,
    paddingVertical: spacing.md,
    paddingHorizontal: spacing.xs,
    alignItems: "center",
    gap: 8,
    minHeight: 86,
    justifyContent: "center",
  },
  serviceIconWrap: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: "rgba(245,158,11,0.12)",
    alignItems: "center",
    justifyContent: "center",
  },
  serviceLabel: {
    color: colors.textSecondary,
    fontSize: 11,
    fontWeight: "600",
    textAlign: "center",
  },

  provRow: { flexDirection: "row", alignItems: "center" },
  avatar: {
    width: 44,
    height: 44,
    borderRadius: 22,
    backgroundColor: colors.bgCardAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
  },
  avatarText: { color: colors.amber, fontWeight: "800", fontSize: 13 },
  provTopLine: { flexDirection: "row", alignItems: "center", justifyContent: "space-between" },
  provName: { color: colors.textPrimary, fontWeight: "700", fontSize: 14 },
  provMeta: { color: colors.textMuted, fontSize: 12, marginTop: 2 },
  provBottomLine: {
    flexDirection: "row",
    justifyContent: "space-between",
    alignItems: "center",
    marginTop: 6,
  },
  inlineRow: { flexDirection: "row", alignItems: "center", gap: 3 },
  provStat: { color: colors.textSecondary, fontSize: 12, fontWeight: "600" },
  provDistance: { color: colors.amber, fontSize: 12, fontWeight: "700" },
});
