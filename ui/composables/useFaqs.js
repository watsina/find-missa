// a list of the FAQs we are going to use hardcoded for now
import { ref } from 'vue';

export const useFaqs = () => {
  const faqs = ref([
    {
      id: 1,
      question: 'What is FindMissa website for?',
      answer:
        'This website helps you find Catholic Mass times across Uganda. Search for parishes by city, town or district to find Mass schedules, contact details, and additional services like Confession or Adoration.',
    },
    {
      id: 2,
      question: 'How do I find Mass times near me?',
      answer:
        "The website enables you to find mass by date, time, city/town name and parish/church name. Enter the name of the city/town you are in with the corresponding date and time and press the search button to get results for the masses in that area. We are working to make this even better and in a later update, we'll add functionality so that you just enable location services to find nearby parishes automatically.",
    },
    {
      id: 3,
      question: 'Are the Mass times up-to-date?',
      answer:
        'We strive to keep Mass times accurate by regularly updating our database with information from parishes. However, schedules may change, especially during holidays. You can check the parish’s profile for their contact details and contact them directly to confirm Mass times. If you notice any discrepancies in the details of a parish you know, please contact us via email "info@findmissa.org" and we will update it as soon as possible.',
    },
    {
      id: 4,
      question: 'Can I find Masses in different languages?',
      answer:
        "Some parishes also offer the Mass in two languages (a local language and English) and this is indicated in the list of the masses. A later update, we'll enable you to use the filter option to search for Masses in specific languages (e.g., English, Luganda, Swahili, etc). ",
    },
    {
      id: 5,
      question: 'What if I can’t find my parish?',
      answer:
        'If your parish isn’t listed, try searching with a nearby city or postal code. You can also contact us via the “Report a Missing Parish” form, and we’ll work to add it.',
    },
    {
      id: 6,
      question: 'Does the site include special services like Confession or Adoration?',
      answer:
        'Yes, many parish listings include schedules for Confession, Eucharistic Adoration, and other sacraments or devotions. Check the parish’s profile for details or contact them directly.',
    },
    {
      id: 7,
      question: 'Can I find Mass times for holidays like Christmas or Easter?',
      answer:
        'Holiday Mass times are often updated closer to the date. Use the search function and check the parish’s profile for special schedules, or contact the parish for confirmation.',
    },
    {
      id: 8,
      question: 'Is this website specific to Uganda?',
      answer:
        'Yes, this site focuses on Catholic parishes in Uganda. For Mass times in neighbouring countries, we recommend checking similar services in other countries, such as Masstimes.org for Kenya or Rwanda.',
    },
    {
      id: 9,
      question: 'Do I need an account to use the site?',
      answer:
        'No account is required to search for Mass times.However, creating a free account lets you save favorite parishes, receive schedule updates, or submit corrections.',
    },
    {
      id: 10,
      question: 'How can I contact a parish?',
      answer:
        'Each parish listing includes contact details like phone numbers, emails, or website links. Use these to reach out directly for questions about Mass times or other services.',
    },
    {
      id: 11,
      question: 'Can I contribute to the site?',
      answer:
        'Yes! If you notice outdated information or want to add a parish, use the “Submit an Update” or “Report a Missing Parish” form. Your help keeps our database accurate.',
    },
    {
      id: 12,
      question: 'Is there a mobile app?',
      answer: 'Currently, our website is mobile-friendly and works on all devices.',
    },
    {
      id: 13,
      question: 'How do I know if a Mass is canceled or changed?',
      answer:
        'Cancellations or changes may not always be reflected immediately. Check the parish’s website, social media, or contact them directly via phone call for real-time updates.',
    },
  ]);

  return { faqs };
};
