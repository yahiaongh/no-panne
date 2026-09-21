import React, { useState } from "react";
import {
  Modal,
  View,
  Text,
  TouchableOpacity,
  TextInput,
  StyleSheet,
} from "react-native";
import { Ionicons } from "@expo/vector-icons";
import { useApp } from "../context/AppContext";
import { colors, radii, spacing, type } from "../theme/theme";
import Button from "../components/Button";
import { REVIEW_TAGS } from "../data/mockData";

export default function ReviewModal({ visible, onClose, onSubmit }) {
  const { t, isRTL } = useApp();
  const [rating, setRating] = useState(0);
  const [selectedTags, setSelectedTags] = useState([]);
  const [amount, setAmount] = useState("");
  const [confirmed, setConfirmed] = useState(false);
  const [submitted, setSubmitted] = useState(false);

  function toggleTag(tag) {
    setSelectedTags((prev) =>
      prev.includes(tag) ? prev.filter((t) => t !== tag) : [...prev, tag]
    );
  }

  function reset() {
    setRating(0);
    setSelectedTags([]);
    setAmount("");
    setConfirmed(false);
    setSubmitted(false);
  }

  function handleSubmit() {
    setSubmitted(true);
    setTimeout(() => {
      onSubmit?.({ rating, tags: selectedTags, amount, confirmed });
      reset();
    }, 900);
  }

  function handleClose() {
    reset();
    onClose?.();
  }

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={handleClose}>
      <View style={styles.overlay}>
        <View style={styles.sheet}>
          <View style={styles.grabber} />

          {submitted ? (
            <View style={styles.thankYouWrap}>
              <Ionicons name="checkmark-circle" size={44} color={colors.success} />
              <Text style={styles.thankYouText}>{t("thankYou")}</Text>
            </View>
          ) : (
            <>
              <Text style={[styles.title, isRTL && { textAlign: "right" }]}>
                {t("reviewTitle")}
              </Text>
              <Text style={[styles.subtitle, isRTL && { textAlign: "right" }]}>
                {t("reviewSubtitle")}
              </Text>

              {/* Stars */}
              <View style={[styles.starsRow, isRTL && { flexDirection: "row-reverse" }]}>
                {[1, 2, 3, 4, 5].map((n) => (
                  <TouchableOpacity key={n} onPress={() => setRating(n)} hitSlop={8}>
                    <Ionicons
                      name={n <= rating ? "star" : "star-outline"}
                      size={34}
                      color={colors.amber}
                      style={{ marginHorizontal: 4 }}
                    />
                  </TouchableOpacity>
                ))}
              </View>

              {/* Tags */}
              <View style={[styles.tagsWrap, isRTL && { flexDirection: "row-reverse" }]}>
                {REVIEW_TAGS.map((tagKey) => {
                  const active = selectedTags.includes(tagKey);
                  return (
                    <TouchableOpacity key={tagKey} onPress={() => toggleTag(tagKey)}>
                      <View style={[styles.tag, active && styles.tagActive]}>
                        <Text style={[styles.tagText, active && styles.tagTextActive]}>
                          {t(tagKey)}
                        </Text>
                      </View>
                    </TouchableOpacity>
                  );
                })}
              </View>

              {/* Cash receipt */}
              <Text style={[styles.fieldLabel, isRTL && { textAlign: "right" }]}>
                {t("receiptLabel")}
              </Text>
              <TextInput
                value={amount}
                onChangeText={setAmount}
                keyboardType="numeric"
                placeholder="0"
                placeholderTextColor={colors.textFaint}
                style={[styles.amountInput, isRTL && { textAlign: "right" }]}
              />

              <TouchableOpacity
                style={[styles.confirmRow, isRTL && { flexDirection: "row-reverse" }]}
                onPress={() => setConfirmed((c) => !c)}
              >
                <View style={[styles.checkbox, confirmed && styles.checkboxActive]}>
                  {confirmed && <Ionicons name="checkmark" size={13} color="#1C1300" />}
                </View>
                <Text style={[styles.confirmText, isRTL && { textAlign: "right" }]}>
                  {t("receiptConfirm")}
                </Text>
              </TouchableOpacity>

              <View style={{ marginTop: spacing.xl, gap: spacing.sm }}>
                <Button
                  label={t("submitReview")}
                  variant="primary"
                  disabled={rating === 0}
                  onPress={handleSubmit}
                />
                <Button label={t("skip")} variant="ghost" onPress={handleClose} />
              </View>
            </>
          )}
        </View>
      </View>
    </Modal>
  );
}

const styles = StyleSheet.create({
  overlay: { flex: 1, backgroundColor: colors.overlay, justifyContent: "flex-end" },
  sheet: {
    backgroundColor: colors.bgSurface,
    borderTopLeftRadius: radii.xl,
    borderTopRightRadius: radii.xl,
    borderWidth: 1,
    borderColor: colors.borderMuted,
    padding: spacing.xl,
    paddingBottom: spacing.xxl,
  },
  grabber: {
    width: 40,
    height: 4,
    borderRadius: 2,
    backgroundColor: colors.border,
    alignSelf: "center",
    marginBottom: spacing.lg,
  },
  title: { ...type.h1, color: colors.textPrimary, fontSize: 19 },
  subtitle: { ...type.small, color: colors.textMuted, marginTop: 4, marginBottom: spacing.lg },

  starsRow: { flexDirection: "row", justifyContent: "center", marginBottom: spacing.xl },

  tagsWrap: { flexDirection: "row", flexWrap: "wrap", gap: 8, marginBottom: spacing.xl },
  tag: {
    borderWidth: 1,
    borderColor: colors.border,
    backgroundColor: colors.bgCard,
    borderRadius: radii.pill,
    paddingVertical: 8,
    paddingHorizontal: 14,
  },
  tagActive: { backgroundColor: "rgba(245,158,11,0.14)", borderColor: colors.amber },
  tagText: { color: colors.textSecondary, fontSize: 12, fontWeight: "600" },
  tagTextActive: { color: colors.amber },

  fieldLabel: { color: colors.textSecondary, fontSize: 12, fontWeight: "600", marginBottom: 8 },
  amountInput: {
    backgroundColor: colors.bgCard,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: radii.md,
    paddingVertical: 12,
    paddingHorizontal: spacing.md,
    color: colors.textPrimary,
    fontSize: 15,
    fontWeight: "700",
    marginBottom: spacing.lg,
  },

  confirmRow: { flexDirection: "row", alignItems: "center", gap: 10 },
  checkbox: {
    width: 20,
    height: 20,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: colors.border,
    alignItems: "center",
    justifyContent: "center",
  },
  checkboxActive: { backgroundColor: colors.amber, borderColor: colors.amber },
  confirmText: { color: colors.textSecondary, fontSize: 12, flex: 1 },

  thankYouWrap: { alignItems: "center", paddingVertical: spacing.xl, gap: spacing.md },
  thankYouText: { color: colors.textPrimary, fontSize: 16, fontWeight: "700" },
});
