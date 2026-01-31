/**
 * Composable for activity schedule formatting and utilities
 */
export default function useActivitySchedule() {
  const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

  const typeDescriptionMap = {
    mass: 'Mass',
    'anticipation-mass': 'Anticipation Mass',
    'traditional-latin-mass': 'Traditional Latin Mass',
    confession: 'Confession',
    adoration: 'Adoration',
    benediction: 'Benediction',
    'mass-of-the-lords-supper': "Mass of the Lord's Supper",
    'good-friday-service': 'Good Friday Service',
    'easter-vigil-mass': 'Easter Vigil Mass',
    'easter-mass': 'Easter Mass',
    'easter-monday-mass': 'Easter Monday Mass',
    'christmas-mass': 'Christmas Mass',
    'christmas-vigil-mass': 'Christmas Vigil Mass',
  };

  /**
   * Format time display from activity
   * @param {Object} activity - The activity object
   * @param {boolean} showEndTime - Whether to include end time (default: false)
   */
  const formatTime = (activity, showEndTime = false) => {
    if (showEndTime && activity.end_time) {
      return `${activity.start_time} - ${activity.end_time}`;
    }
    return activity.start_time;
  };

  /**
   * Format type description to human readable
   */
  const formatTypeDescription = (type) => {
    return typeDescriptionMap[type] || type;
  };

  /**
   * Format activity description including time, language, and type
   * @param {Object} activity - The activity object
   * @param {boolean} showEndTime - Whether to include end time (default: false)
   */
  const formatActivityDescription = (activity, showEndTime = false) => {
    const parts = [];
    parts.push(formatTime(activity, showEndTime));
    if (activity.language) {
      parts.push(`(${activity.language})`);
    }
    if (
      activity.activity_type_description &&
      activity.activity_type_description !== activity.type
    ) {
      parts.push(`- ${formatTypeDescription(activity.activity_type_description)}`);
    }
    return parts.join(' ');
  };

  /**
   * Format date for one-time events
   */
  const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  };

  /**
   * Check if v2 schedule has any data for a single activity type
   * V2 format uses: weekdays, saturday, sunday, daily, specific, monthly_nth
   */
  const hasActivityTypeV2 = (activityType) => {
    if (!activityType) return false;
    return (
      (activityType.weekdays || []).length > 0 ||
      (activityType.saturday || []).length > 0 ||
      (activityType.sunday || []).length > 0 ||
      (activityType.daily || []).length > 0 ||
      (activityType.specific || []).length > 0 ||
      (activityType.monthly_nth || []).length > 0
    );
  };

  /**
   * Check if a schedule has any activities (mass, confession, or adoration)
   * Updated for v2 format
   */
  const hasActivities = (schedule) => {
    if (!schedule) return false;
    return (
      hasActivityTypeV2(schedule.mass) ||
      hasActivityTypeV2(schedule.confession) ||
      hasActivityTypeV2(schedule.adoration)
    );
  };

  return {
    daysOfWeek,
    formatTime,
    formatTypeDescription,
    formatActivityDescription,
    formatDate,
    hasActivityTypeV2,
    hasActivities,
  };
}
