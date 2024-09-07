import dayjs from 'dayjs';
import Duration from 'dayjs/plugin/duration';
import RelativeTime from 'dayjs/plugin/relativeTime';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import isoWeek from 'dayjs/plugin/isoWeek';
import utc from 'dayjs/plugin/utc';
import timezone from 'dayjs/plugin/timezone';
import advancedFormat from 'dayjs/plugin/advancedFormat';
import weekOfYear from 'dayjs/plugin/weekOfYear';

dayjs.extend(Duration);

dayjs.extend(RelativeTime);

dayjs.extend(localizedFormat);

dayjs.extend(isoWeek);

dayjs.extend(utc);

dayjs.extend(timezone);

dayjs.extend(advancedFormat);

dayjs.extend(weekOfYear);

export default dayjs;
