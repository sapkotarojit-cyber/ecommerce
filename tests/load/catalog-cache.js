import http from 'k6/http';
import { check, sleep } from 'k6';

const BASE_URL = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export const options = {
  scenarios: {
    catalog_load: {
      executor: 'constant-vus',
      vus: 50,
      duration: '5m',
    },
  },
  thresholds: {
    http_req_duration: ['p(95)<2000'],
    checks: ['rate>0.99'],
  },
};

export default function () {
  const products = http.get(`${BASE_URL}/api/products`, {
    tags: { endpoint: 'products' },
    timeout: '15s',
  });

  check(products, {
    'products status is 200': (r) => r.status === 200,
    'products response is JSON': (r) =>
      (r.headers['Content-Type'] || '').includes('application/json'),
  });

  const categories = http.get(`${BASE_URL}/api/categories`, {
    tags: { endpoint: 'categories' },
    timeout: '15s',
  });

  check(categories, {
    'categories status is 200': (r) => r.status === 200,
    'categories response is JSON': (r) =>
      (r.headers['Content-Type'] || '').includes('application/json'),
  });

  sleep(1);
}
