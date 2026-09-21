import React, { useEffect, useRef, useState } from "react";
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  Animated,
  Easing,
  Linking,
  StyleSheet,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useApp } from "../context/AppContext";
import { colors, radii, spacing, type } from "../theme/theme";
import Button from "../components/Button";
import { Card, Badge } from "../components/Primitives";
import Timeline from "../components/Timeline";
import { ACTIVE_PROVIDER, MISSION_STATUSES } from "../data/mockData";

const SEARCH_DURATION_MS = 2600;

export default function TrackingScreen({ onOpenReview }) {
  const { t, isRTL } = useApp();
  const [phase, setPhase] = useState("searching"); // searching | found
  const [statusIndex, setStatusIndex] = useState(0);

  const spin = useRef(new Animated.Value(0)).current;
  const ping = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    const spinLoop = Animated.loop(
      Animated.timing(spin, {
        toValue: 1,
        duration: 2200,
        easing: Easing.linear,
        useNativeDriver: true,
      })
    );
    const pingLoop = Animated.loop(
      Animated.timing(ping, {
        toValue: 1,
        duration: 1600,
        easing: Easing.out(Easing.ease),
        useNativeDriver: true,
      })
    );
    spinLoop.start();
    pingLoop.start();

    const timer = setTimeout(() => setPhase("found"), SEARCH_DURATION_MS);
    return () => {
      spinLoop.stop();
      pingLoop.stop();
      clearTimeout(timer);
    };
  }, []);

  const rotate = spin.interpolate({ inputRange: [0, 1], outputRange: ["0deg", "360deg"] });
  const pingScale = ping.interpolate({ inputRange: [0, 1], outputRange: [0.4, 1.6] });
  const pingOpacity = ping.interpolate({ inputRange: [0, 1], outputRange: [0.5, 0] });

  const isComplete = statusIndex === MISSION_STATUSES.length - 1;

  return (
    <View style={{ flex: 1, backgroundColor: colors.bgDeep }}>
      <ScrollView
        contentContainerStyle={{ padding: spacing.lg, paddingBottom: spacing.xxxl }}
        showsVerticalScrollIndicator={false}
      >
        {phase === "searching" && (
          <View style={styles.radarWrap}>
            <Animated.View
              style={[
                styles.pingRing,
                { transform: [{ scale: pingScale }], opacity: pingOpacity },
              ]}
            />
            <View style={styles.radarCircleOuter}>
              <Animated.View style={[styles.radarSweep, { transform: [{ rotate }] }]} />
              <View style={styles.radarCircleInner}>
                <Ionicons name="car-sport" size={26} color={colors.amber} />
              </View>
            </View>
            <Text style={styles.searchingText}>{t("searching")}</Text>
          </View>
        )}

        {phase === "found" && (
          <>
            <View style={styles.foundBanner}>
              <Ionicons name="checkmark-circle" size={18} color={colors.success} />
              <Text style={styles.foundBannerText}>{t("providerFound")}</Text>
            </View>

            {/* Provider card */}
            <Card style={{ marginTop: spacing.lg }}>
              <View style={[styles.provRow, isRTL && { flexDirection: "row-reverse" }]}>
                <View style={styles.avatar}>
                  <Text style={styles.avatarText}>{ACTIVE_PROVIDER.photo}</Text>
                </View>
                <View style={{ flex: 1, marginHorizontal: spacing.md }}>
                  <Text style={[styles.provName, isRTL && { textAlign: "right" }]}>
                    {ACTIVE_PROVIDER.name}
                  </Text>
                  <View style={[styles.inlineRow, isRTL && { flexDirection: "row-reverse" }]}>
                    <Ionicons name="star" size={12} color={colors.amber} />
                    <Text style={styles.provStat}>
                      {ACTIVE_PROVIDER.rating} ({ACTIVE_PROVIDER.reviews})
                    </Text>
                  </View>
                  <Text style={[styles.provMeta, isRTL && { textAlign: "right" }]}>
                    {t("plate")}: {ACTIVE_PROVIDER.plate}
                  </Text>
                </View>
                <TouchableOpacity
                  style={styles.callBtn}
                  onPress={() => Linking.openURL(`tel:${ACTIVE_PROVIDER.phone}`)}
                >
                  <Ionicons name="call" size={18} color="#1C1300" />
                </TouchableOpacity>
              </View>
            </Card>

            {/* Timeline */}
            <Card style={{ marginTop: spacing.lg }}>
              <Timeline currentIndex={statusIndex} />
              {statusIndex < MISSION_STATUSES.length - 1 && (
                <View style={[styles.etaRow, isRTL && { flexDirection: "row-reverse" }]}>
                  <Ionicons name="time-outline" size={14} color={colors.amber} />
                  <Text style={styles.etaText}>{t("etaLabel")}</Text>
                </View>
              )}
            </Card>

            {/* Payment reminder */}
            <View style={[styles.paymentPill, isRTL && { flexDirection: "row-reverse" }]}>
              <Ionicons name="cash-outline" size={15} color={colors.amber} />
              <Text style={[styles.paymentText, isRTL && { textAlign: "right" }]}>
                {t("paymentReminder")}
              </Text>
            </View>

            {/* Demo controls */}
            <View style={{ marginTop: spacing.xl }}>
              {!isComplete ? (
                <Button
                  label={t("advanceStatus")}
                  variant="dark"
                  icon="play-forward"
                  onPress={() => setStatusIndex((i) => Math.min(i + 1, MISSION_STATUSES.length - 1))}
                />
              ) : (
                <View style={{ gap: spacing.md }}>
                  <Badge label={t("missionComplete")} tone="success" style={{ alignSelf: "center" }} />
                  <Button label={t("leaveReview")} variant="primary" icon="star" onPress={onOpenReview} />
                </View>
              )}
            </View>
          </>
        )}
      </ScrollView>
    </View>
  );
}

const styles = StyleSheet.create({
  radarWrap: { alignItems: "center", justifyContent: "center", paddingVertical: spacing.xxxl },
  pingRing: {
    position: "absolute",
    width: 140,
    height: 140,
    borderRadius: 70,
    borderWidth: 2,
    borderColor: colors.amber,
  },
  radarCircleOuter: {
    width: 140,
    height: 140,
    borderRadius: 70,
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: colors.bgCard,
    alignItems: "center",
    justifyContent: "center",
    overflow: "hidden",
  },
  radarSweep: {
    position: "absolute",
    width: 140,
    height: 70,
    top: 0,
    left: 0,
    backgroundColor: "rgba(245,158,11,0.18)",
    borderTopLeftRadius: 70,
    borderTopRightRadius: 70,
  },
  radarCircleInner: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: colors.bgSurface,
    borderWidth: 1,
    borderColor: colors.borderMuted,
    alignItems: "center",
    justifyContent: "center",
  },
  searchingText: {
    marginTop: spacing.xl,
    color: colors.textSecondary,
    fontSize: 14,
    fontWeight: "600",
    textAlign: "center",
  },

  foundBanner: {
    flexDirection: "row",
    alignItems: "center",
    justifyContent: "center",
    gap: 8,
    backgroundColor: colors.successBg,
    borderWidth: 1,
    borderColor: "#1C4A2C",
    borderRadius: radii.md,
    paddingVertical: 12,
  },
  foundBannerText: { color: colors.success, fontWeight: "700", fontSize: 13 },

  provRow: { flexDirection: "row", alignItems: "center" },
  avatar: {
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: colors.bgCardAlt,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
  },
  avatarText: { color: colors.amber, fontWeight: "800", fontSize: 13 },
  provName: { color: colors.textPrimary, fontWeight: "700", fontSize: 15 },
  provMeta: { color: colors.textMuted, fontSize: 12, marginTop: 3 },
  inlineRow: { flexDirection: "row", alignItems: "center", gap: 3, marginTop: 3 },
  provStat: { color: colors.textSecondary, fontSize: 12, fontWeight: "600" },
  callBtn: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: colors.amber,
    alignItems: "center",
    justifyContent: "center",
  },

  etaRow: {
    flexDirection: "row",
    alignItems: "center",
    gap: 6,
    justifyContent: "center",
    marginTop: spacing.lg,
  },
  etaText: { color: colors.amber, fontSize: 12, fontWeight: "700" },

  paymentPill: {
    flexDirection: "row",
    alignItems: "center",
    gap: 8,
    backgroundColor: colors.bgCardAlt,
    borderWidth: 1,
    borderColor: colors.borderMuted,
    borderRadius: radii.pill,
    paddingVertical: 10,
    paddingHorizontal: spacing.md,
    marginTop: spacing.lg,
  },
  paymentText: { color: colors.textMuted, fontSize: 11, fontWeight: "600", flex: 1 },
});
