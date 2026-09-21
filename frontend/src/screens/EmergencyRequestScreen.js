import React, { useState } from "react";
import {
  View,
  Text,
  ScrollView,
  TouchableOpacity,
  TextInput,
  StyleSheet,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useApp } from "../context/AppContext";
import { colors, radii, spacing, type } from "../theme/theme";
import Button from "../components/Button";
import { Card, Badge } from "../components/Primitives";
import { ISSUE_TAGS } from "../data/mockData";

export default function EmergencyRequestScreen({ onCancel, onLaunchSearch }) {
  const { t, isRTL } = useApp();
  const [step, setStep] = useState(1);
  const [selectedIssue, setSelectedIssue] = useState(null);
  const [notes, setNotes] = useState("");

  const canAdvanceFromStep1 = !!selectedIssue;

  return (
    <View style={{ flex: 1, backgroundColor: colors.bgDeep }}>
      {/* Step header */}
      <View style={[styles.topRow, isRTL && { flexDirection: "row-reverse" }]}>
        <TouchableOpacity onPress={onCancel} style={styles.closeBtn}>
          <Ionicons name="close" size={20} color={colors.textSecondary} />
        </TouchableOpacity>
        <View style={{ flex: 1, marginHorizontal: spacing.md }}>
          <Text style={styles.stepLabel}>
            {t("step")} {step} {t("of")} 3
          </Text>
          <View style={styles.progressTrack}>
            {[1, 2, 3].map((i) => (
              <View
                key={i}
                style={[
                  styles.progressSeg,
                  i <= step && { backgroundColor: colors.amber },
                ]}
              />
            ))}
          </View>
        </View>
      </View>

      <ScrollView
        contentContainerStyle={{ padding: spacing.lg, paddingBottom: spacing.xxl }}
        showsVerticalScrollIndicator={false}
      >
        {step === 1 && (
          <>
            <Text style={[styles.title, isRTL && { textAlign: "right" }]}>
              {t("issueStepTitle")}
            </Text>
            <Text style={[styles.subtitle, isRTL && { textAlign: "right" }]}>
              {t("issueStepSubtitle")}
            </Text>
            <View style={{ gap: spacing.md, marginTop: spacing.lg }}>
              {ISSUE_TAGS.map((issue) => {
                const active = selectedIssue === issue.id;
                return (
                  <TouchableOpacity
                    key={issue.id}
                    onPress={() => setSelectedIssue(issue.id)}
                    activeOpacity={0.85}
                  >
                    <View
                      style={[
                        styles.issueCard,
                        isRTL && { flexDirection: "row-reverse" },
                        active && styles.issueCardActive,
                      ]}
                    >
                      <View
                        style={[
                          styles.issueIconWrap,
                          active && { backgroundColor: colors.amber },
                        ]}
                      >
                        <Ionicons
                          name={issue.icon}
                          size={19}
                          color={active ? "#1C1300" : colors.amber}
                        />
                      </View>
                      <Text
                        style={[
                          styles.issueLabel,
                          isRTL && { textAlign: "right" },
                          active && { color: colors.textPrimary },
                        ]}
                      >
                        {t(issue.labelKey)}
                      </Text>
                      {active && (
                        <Ionicons
                          name="checkmark-circle"
                          size={20}
                          color={colors.amber}
                          style={isRTL ? { marginEnd: "auto" } : { marginStart: "auto" }}
                        />
                      )}
                    </View>
                  </TouchableOpacity>
                );
              })}
            </View>
          </>
        )}

        {step === 2 && (
          <>
            <Text style={[styles.title, isRTL && { textAlign: "right" }]}>
              {t("locationStepTitle")}
            </Text>
            <Text style={[styles.subtitle, isRTL && { textAlign: "right" }]}>
              {t("locationStepSubtitle")}
            </Text>

            {/* Stylized mock map */}
            <View style={styles.mapWrap}>
              <MockMapGrid />
              <View style={styles.mapPinWrap}>
                <View style={styles.mapPinPulse} />
                <Ionicons name="location" size={30} color={colors.amber} />
              </View>
              <View style={[styles.mapPinLabel, isRTL && { alignItems: "flex-end" }]}>
                <Badge label={t("gpsPin")} tone="amber" />
              </View>
            </View>

            <TextInput
              value={notes}
              onChangeText={setNotes}
              placeholder={t("notesPlaceholder")}
              placeholderTextColor={colors.textFaint}
              multiline
              style={[
                styles.notesInput,
                isRTL && { textAlign: "right", writingDirection: "rtl" },
              ]}
            />
          </>
        )}

        {step === 3 && (
          <>
            <Text style={[styles.title, isRTL && { textAlign: "right" }]}>
              {t("locationStepTitle")}
            </Text>
            <Card style={{ marginTop: spacing.lg }}>
              <SummaryRow
                icon="alert-circle"
                label={t(ISSUE_TAGS.find((i) => i.id === selectedIssue)?.labelKey || "issueEngine")}
                isRTL={isRTL}
              />
              <View style={styles.divider} />
              <SummaryRow icon="location" label={t("gpsPin")} isRTL={isRTL} />
              {!!notes && (
                <>
                  <View style={styles.divider} />
                  <SummaryRow icon="document-text" label={notes} isRTL={isRTL} />
                </>
              )}
              <View style={styles.divider} />
              <SummaryRow icon="navigate-circle" label={t("radiusLabel")} isRTL={isRTL} />
            </Card>
          </>
        )}
      </ScrollView>

      <View style={styles.footer}>
        {step > 1 && (
          <View style={{ marginBottom: spacing.sm }}>
            <Button
              label={t("back")}
              variant="ghost"
              onPress={() => setStep((s) => s - 1)}
            />
          </View>
        )}
        {step < 3 && (
          <Button
            label={t("next")}
            variant="primary"
            icon={isRTL ? "arrow-back" : "arrow-forward"}
            iconPosition="end"
            disabled={step === 1 && !canAdvanceFromStep1}
            onPress={() => setStep((s) => s + 1)}
          />
        )}
        {step === 3 && (
          <Button
            label={`${t("launchSearch")} (${t("radiusLabel")})`}
            variant="danger"
            icon="search"
            onPress={onLaunchSearch}
          />
        )}
      </View>
    </View>
  );
}

function SummaryRow({ icon, label, isRTL }) {
  return (
    <View style={[styles.summaryRow, isRTL && { flexDirection: "row-reverse" }]}>
      <Ionicons name={icon} size={17} color={colors.amber} />
      <Text style={[styles.summaryText, isRTL && { textAlign: "right" }]}>{label}</Text>
    </View>
  );
}

function MockMapGrid() {
  const lines = new Array(6).fill(0);
  return (
    <View style={StyleSheet.absoluteFill}>
      {lines.map((_, i) => (
        <View key={`h${i}`} style={[styles.gridLineH, { top: `${(i + 1) * 14}%` }]} />
      ))}
      {lines.map((_, i) => (
        <View key={`v${i}`} style={[styles.gridLineV, { left: `${(i + 1) * 14}%` }]} />
      ))}
      <View style={styles.mapRoad} />
    </View>
  );
}

const styles = StyleSheet.create({
  topRow: {
    flexDirection: "row",
    alignItems: "center",
    padding: spacing.lg,
    paddingBottom: spacing.md,
  },
  closeBtn: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: colors.bgCard,
    borderWidth: 1,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
  },
  stepLabel: { color: colors.textMuted, fontSize: 11, fontWeight: "700", marginBottom: 6 },
  progressTrack: { flexDirection: "row", gap: 6 },
  progressSeg: { flex: 1, height: 4, borderRadius: 2, backgroundColor: colors.bgCardAlt },

  title: { ...type.h1, color: colors.textPrimary, fontSize: 22 },
  subtitle: { ...type.body, color: colors.textMuted, marginTop: 6 },

  issueCard: {
    flexDirection: "row",
    alignItems: "center",
    gap: 12,
    backgroundColor: colors.bgCard,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.md,
    padding: spacing.md,
  },
  issueCardActive: { borderColor: colors.amber, backgroundColor: "rgba(245,158,11,0.08)" },
  issueIconWrap: {
    width: 38,
    height: 38,
    borderRadius: 10,
    backgroundColor: "rgba(245,158,11,0.12)",
    alignItems: "center",
    justifyContent: "center",
  },
  issueLabel: { color: colors.textSecondary, fontWeight: "600", fontSize: 14 },

  mapWrap: {
    height: 190,
    borderRadius: radii.lg,
    backgroundColor: "#0A1526",
    borderWidth: 1,
    borderColor: colors.border,
    marginTop: spacing.lg,
    overflow: "hidden",
    alignItems: "center",
    justifyContent: "center",
  },
  gridLineH: {
    position: "absolute",
    left: 0,
    right: 0,
    height: 1,
    backgroundColor: "rgba(148,163,184,0.08)",
  },
  gridLineV: {
    position: "absolute",
    top: 0,
    bottom: 0,
    width: 1,
    backgroundColor: "rgba(148,163,184,0.08)",
  },
  mapRoad: {
    position: "absolute",
    left: -20,
    right: -20,
    top: "55%",
    height: 22,
    backgroundColor: "rgba(148,163,184,0.10)",
    transform: [{ rotate: "-8deg" }],
  },
  mapPinWrap: { alignItems: "center", justifyContent: "center" },
  mapPinPulse: {
    position: "absolute",
    width: 46,
    height: 46,
    borderRadius: 23,
    backgroundColor: "rgba(245,158,11,0.18)",
  },
  mapPinLabel: { position: "absolute", bottom: 12, left: 12, right: 12 },

  notesInput: {
    marginTop: spacing.lg,
    minHeight: 84,
    backgroundColor: colors.bgCard,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.md,
    padding: spacing.md,
    color: colors.textPrimary,
    fontSize: 14,
    textAlignVertical: "top",
  },

  summaryRow: { flexDirection: "row", alignItems: "center", gap: 10, paddingVertical: 10 },
  summaryText: { color: colors.textSecondary, fontSize: 13, flex: 1 },
  divider: { height: 1, backgroundColor: colors.border },

  footer: {
    padding: spacing.lg,
    borderTopWidth: 1,
    borderTopColor: colors.borderMuted,
    backgroundColor: colors.bgDeep,
  },
});
