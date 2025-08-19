// a list of the FAQs we are going to use hardcoded for now
import { ref } from 'vue';

export const useFaqs = () => {
  const faqs = ref([
    {
      id: 1,
      question: 'What is FindMissa website for?',
      answer:
        'This website helps you find Catholic Mass times across Uganda. Search for parishes by city, town or district to find Mass schedules, contact details, and additional services like Confession or Adoration. It is a free service provided by team of lay people to help Catholics and visitors find Mass times easily.',
    },
    {
      id: 2,
      question: 'How do I find Mass times near me?',
      answer:
        "The website enables you to find Mass by date, time, city/town name and parish/church name. Enter the name of the city/town you are in with the corresponding date and time and press the search button to get results for the Masses in that area. We are working to make this even better and in a later update, we'll add functionality so that you just enable location services to find nearby parishes automatically.",
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
        "Some parishes offer the Mass in two languages (a local language and English) and this is indicated in the list of the Masses. A later update, we'll enable you to use the filter option to search for Masses in specific languages (e.g., English, Luganda, Swahili, etc). ",
    },
    {
      id: 5,
      question: 'What if I can’t find my parish?',
      answer:
        'If your parish isn’t listed, you can add its details via our google form (<a href="https://forms.gle/kGiX1A36dW3is1BJA">https://forms.gle/kGiX1A36dW3is1BJA</a>), and we’ll work to add it as soon as possible. Make sure to add a phone contact of the parish priest or catechist so that we can verify the details before adding it to the site.',
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
        'Yes, this site at the moment focuses on Catholic parishes in Uganda. For Mass times in neighbouring countries, we recommend checking similar services in other countries, such as Masstimes.org for Kenya or Rwanda. Depending on how things go, we may expand to include other countries in the future.',
    },
    {
      id: 9,
      question: 'Do I need an account to use the site?',
      answer: 'No account is required to search for Mass times.',
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
        'Yes! If you notice outdated information or want to add a parish, use you can submit updates via our google form (<a href="https://forms.gle/kGiX1A36dW3is1BJA">https://forms.gle/kGiX1A36dW3is1BJA</a>). Your help keeps our database accurate.',
    },
    {
      id: 12,
      question: 'Is there a mobile app?',
      answer:
        'Currently, our website is mobile-friendly and works on all devices and we do not plan to make a mobile app at the moment.',
    },
    {
      id: 13,
      question: 'How do I know if a Mass is canceled or changed?',
      answer:
        "Using the search feature will return only the Masses that are available for the specified date so if a Mass that is usually preset for that day is missing, then it's schedule was changed. Cancellations or changes may not always be reflected immediately. Check the parish’s website, social media, or contact them directly via phone call for real-time updates.",
    },
    {
      id: 14,
      question: 'Is there a way to save my favorite parishes?',
      answer:
        'Currently, the site does not have a feature to save favorite parishes. However, you can bookmark the parish pages in your web browser for easy access.',
    },
    {
      id: 15,
      question: 'How can I support FindMissa?',
      answer:
        'You can support us by spreading the word about FindMissa, sharing it with friends and family, and providing feedback on how we can improve the site. If you would like to contribute financially to help us cover the costs for running the project, please reachout to us on info@findmissa.org.',
    },
  ]);

  return { faqs };
};
