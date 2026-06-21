import { storage, KEYS } from '../services/storage'

export function initSeedData() {
  if (storage.get(KEYS.SEEDED)) return

  const users = [
    { id: 'u1', name: 'أحمد محمد', email: 'customer@test.com', password: '123456', role: 'customer', phone: '0599123456', storeId: null },
    { id: 'u2', name: 'صيدلية الشفاء', email: 'pharmacy@test.com', password: '123456', role: 'provider', phone: '0599111222', storeId: 's1' },
    { id: 'u3', name: 'سوبر ماركت الأمل', email: 'supermarket@test.com', password: '123456', role: 'provider', phone: '0599333444', storeId: 's3' },
    { id: 'u4', name: 'محمد السائق', email: 'driver@test.com', password: '123456', role: 'driver', phone: '0599555666', storeId: null, rating: 4.9, totalDeliveries: 142 },
    { id: 'u5', name: 'صيدلية النور', email: 'pharmacy2@test.com', password: '123456', role: 'provider', phone: '0599777888', storeId: 's2' },
  ]

  const stores = [
    { id: 's1', name: 'صيدلية الشفاء', category: 'pharmacy', categoryLabel: 'صيدلية', rating: 4.8, reviewCount: 234, deliveryTime: '20-30 دقيقة', deliveryFee: 5, minOrder: 30, providerId: 'u2', isOpen: true, description: 'صيدلية معتمدة بخبرة 15 عاماً في تقديم الأدوية والمنتجات الصحية' },
    { id: 's2', name: 'صيدلية النور', category: 'pharmacy', categoryLabel: 'صيدلية', rating: 4.6, reviewCount: 178, deliveryTime: '25-40 دقيقة', deliveryFee: 5, minOrder: 25, providerId: 'u5', isOpen: true, description: 'نوفر أفضل الأدوية والمستلزمات الطبية بأسعار مناسبة' },
    { id: 's3', name: 'سوبر ماركت الأمل', category: 'supermarket', categoryLabel: 'سوبر ماركت', rating: 4.5, reviewCount: 312, deliveryTime: '30-45 دقيقة', deliveryFee: 8, minOrder: 50, providerId: 'u3', isOpen: true, description: 'كل احتياجاتك اليومية من مواد غذائية طازجة وبضائع متنوعة' },
    { id: 's4', name: 'سوبر ماركت الرشيد', category: 'supermarket', categoryLabel: 'سوبر ماركت', rating: 4.3, reviewCount: 145, deliveryTime: '35-50 دقيقة', deliveryFee: 8, minOrder: 50, providerId: null, isOpen: false, description: 'سوبر ماركت متكامل بتشكيلة واسعة من المنتجات المحلية والمستوردة' },
    { id: 's5', name: 'محل التقنية', category: 'electronics', categoryLabel: 'إلكترونيات', rating: 4.7, reviewCount: 89, deliveryTime: '40-60 دقيقة', deliveryFee: 10, minOrder: 100, providerId: null, isOpen: true, description: 'أحدث الإكسسوارات والأجهزة الإلكترونية بأفضل الأسعار' },
    { id: 's6', name: 'متجر الأناقة', category: 'fashion', categoryLabel: 'ملابس وأزياء', rating: 4.4, reviewCount: 201, deliveryTime: '45-60 دقيقة', deliveryFee: 12, minOrder: 80, providerId: null, isOpen: true, description: 'أحدث صيحات الموضة للرجال والسيدات بتصاميم عصرية أنيقة' },
  ]

  const products = [
    // صيدلية الشفاء
    { id: 'p1', storeId: 's1', name: 'بنادول أكسترا', category: 'مسكنات', price: 15, description: 'أقراص لتخفيف الألم والحرارة، 24 قرص', inStock: true },
    { id: 'p2', storeId: 's1', name: 'فيتامين C فوار', category: 'فيتامينات', price: 28, description: 'فيتامين سي 1000 مجم، 30 قرص فوار', inStock: true },
    { id: 'p3', storeId: 's1', name: 'أسبرين 100', category: 'مسكنات', price: 12, description: 'أقراص أسبرين لصحة القلب، 30 قرص', inStock: true },
    { id: 'p4', storeId: 's1', name: 'جل تعقيم اليدين', category: 'نظافة', price: 18, description: 'جل مطهر للأيدي 500 مل بعطر اللافندر', inStock: true },
    { id: 'p5', storeId: 's1', name: 'كمامات طبية', category: 'معدات', price: 25, description: 'كمامات طبية معقمة، عبوة 10 قطع', inStock: true },
    { id: 'p6', storeId: 's1', name: 'ميزان الضغط الرقمي', category: 'أجهزة', price: 120, description: 'جهاز قياس ضغط الدم الرقمي الدقيق', inStock: true },
    // صيدلية النور
    { id: 'p7', storeId: 's2', name: 'أوميغا 3', category: 'فيتامينات', price: 45, description: 'كبسولات أوميغا 3 لصحة القلب، 60 كبسولة', inStock: true },
    { id: 'p8', storeId: 's2', name: 'شامبو طبي للقشرة', category: 'عناية', price: 35, description: 'شامبو علاجي ضد القشرة وتساقط الشعر', inStock: true },
    { id: 'p9', storeId: 's2', name: 'كريم مرطب للبشرة', category: 'عناية', price: 22, description: 'كريم مرطب للبشرة الجافة 200 مل', inStock: true },
    { id: 'p10', storeId: 's2', name: 'بخاخ الأنف', category: 'أدوية', price: 30, description: 'بخاخ لعلاج احتقان الأنف', inStock: false },
    { id: 'p11', storeId: 's2', name: 'حبوب الكالسيوم', category: 'فيتامينات', price: 38, description: 'مكمل غذائي كالسيوم مع فيتامين D، 60 قرص', inStock: true },
    // سوبر ماركت الأمل
    { id: 'p12', storeId: 's3', name: 'خبز العيش الطازج', category: 'مخبوزات', price: 5, description: 'خبز طازج من المخبز يومياً', inStock: true },
    { id: 'p13', storeId: 's3', name: 'حليب طازج', category: 'ألبان', price: 8, description: 'حليب طازج كامل الدسم 1 لتر', inStock: true },
    { id: 'p14', storeId: 's3', name: 'بيض بلدي', category: 'بروتين', price: 15, description: 'بيض بلدي طازج مباشر من المزرعة، 12 بيضة', inStock: true },
    { id: 'p15', storeId: 's3', name: 'أرز مصري فاخر', category: 'حبوب', price: 12, description: 'أرز أبيض مصري عالي الجودة، 1 كيلو', inStock: true },
    { id: 'p16', storeId: 's3', name: 'زيت زيتون بكر', category: 'زيوت', price: 45, description: 'زيت زيتون بكر ممتاز معصور على البارد، 750 مل', inStock: true },
    { id: 'p17', storeId: 's3', name: 'طماطم طازجة', category: 'خضروات', price: 8, description: 'طماطم طازجة محلية، 1 كيلو', inStock: true },
    { id: 'p18', storeId: 's3', name: 'موز', category: 'فواكه', price: 10, description: 'موز طازج مستورد، 1 كيلو', inStock: true },
    { id: 'p19', storeId: 's3', name: 'جبن أبيض طازج', category: 'ألبان', price: 22, description: 'جبن أبيض طازج محلي الصنع، 500 جرام', inStock: true },
    { id: 'p20', storeId: 's3', name: 'عصير برتقال طازج', category: 'مشروبات', price: 18, description: 'عصير برتقال طبيعي 100% بدون إضافات، 1 لتر', inStock: true },
    // محل التقنية
    { id: 'p21', storeId: 's5', name: 'سماعات بلوتوث لاسلكية', category: 'سماعات', price: 120, description: 'سماعات بلوتوث عالية الجودة مع خاصية إلغاء الضوضاء', inStock: true },
    { id: 'p22', storeId: 's5', name: 'شاحن سريع 65W', category: 'شواحن', price: 45, description: 'شاحن سريع 65 واط مع كابل USB-C', inStock: true },
    { id: 'p23', storeId: 's5', name: 'كابل USB-C متين', category: 'كوابل', price: 20, description: 'كابل USB-C مقاوم للكسر، 2 متر', inStock: true },
    // متجر الأناقة
    { id: 'p24', storeId: 's6', name: 'قميص كلاسيك رجالي', category: 'قمصان', price: 85, description: 'قميص رجالي أنيق قطن 100%، متوفر بألوان متعددة', inStock: true },
    { id: 'p25', storeId: 's6', name: 'بنطلون جينز Slim Fit', category: 'بنطلونات', price: 150, description: 'بنطلون جينز مريح slim fit بتصميم عصري', inStock: true },
  ]

  const threeAgo = new Date(Date.now() - 86400000 * 3).toISOString()
  const twoAgo = new Date(Date.now() - 86400000 * 2).toISOString()
  const hourAgo = new Date(Date.now() - 3600000).toISOString()

  const orders = [
    {
      id: 'ord1',
      customerId: 'u1',
      storeId: 's1',
      storeName: 'صيدلية الشفاء',
      driverId: 'u4',
      items: [
        { productId: 'p1', name: 'بنادول أكسترا', price: 15, quantity: 2 },
        { productId: 'p2', name: 'فيتامين C فوار', price: 28, quantity: 1 },
      ],
      subtotal: 58,
      deliveryFee: 5,
      total: 63,
      status: 'delivered',
      address: 'شارع الاستقلال، المنزل رقم 12، رام الله',
      notes: '',
      createdAt: threeAgo,
      deliveredAt: threeAgo,
    },
    {
      id: 'ord2',
      customerId: 'u1',
      storeId: 's3',
      storeName: 'سوبر ماركت الأمل',
      driverId: null,
      items: [
        { productId: 'p12', name: 'خبز العيش الطازج', price: 5, quantity: 2 },
        { productId: 'p13', name: 'حليب طازج', price: 8, quantity: 3 },
        { productId: 'p14', name: 'بيض بلدي', price: 15, quantity: 1 },
      ],
      subtotal: 49,
      deliveryFee: 8,
      total: 57,
      status: 'preparing',
      address: 'شارع الاستقلال، المنزل رقم 12، رام الله',
      notes: 'يرجى التوصيل قبل الظهر',
      createdAt: hourAgo,
      deliveredAt: null,
    },
    {
      id: 'ord3',
      customerId: 'u1',
      storeId: 's2',
      storeName: 'صيدلية النور',
      driverId: 'u4',
      items: [
        { productId: 'p7', name: 'أوميغا 3', price: 45, quantity: 1 },
        { productId: 'p9', name: 'كريم مرطب للبشرة', price: 22, quantity: 2 },
      ],
      subtotal: 89,
      deliveryFee: 5,
      total: 94,
      status: 'delivered',
      address: 'شارع النهضة، مبنى الأعمال، الطابق 3، رام الله',
      notes: '',
      createdAt: twoAgo,
      deliveredAt: twoAgo,
    },
    {
      id: 'ord4',
      customerId: 'u1',
      storeId: 's1',
      storeName: 'صيدلية الشفاء',
      driverId: null,
      items: [
        { productId: 'p4', name: 'جل تعقيم اليدين', price: 18, quantity: 3 },
        { productId: 'p5', name: 'كمامات طبية', price: 25, quantity: 1 },
      ],
      subtotal: 79,
      deliveryFee: 5,
      total: 84,
      status: 'ready',
      address: 'شارع الاستقلال، المنزل رقم 12، رام الله',
      notes: '',
      createdAt: new Date(Date.now() - 1200000).toISOString(),
      deliveredAt: null,
    },
  ]

  const addresses = [
    { id: 'addr1', userId: 'u1', label: 'المنزل', address: 'شارع الاستقلال، المنزل رقم 12، رام الله', isDefault: true },
    { id: 'addr2', userId: 'u1', label: 'العمل', address: 'شارع النهضة، مبنى الأعمال، الطابق 3، رام الله', isDefault: false },
  ]

  storage.set(KEYS.USERS, users)
  storage.set(KEYS.STORES, stores)
  storage.set(KEYS.PRODUCTS, products)
  storage.set(KEYS.ORDERS, orders)
  storage.set(KEYS.ADDRESSES, addresses)
  storage.set(KEYS.SEEDED, true)
}
