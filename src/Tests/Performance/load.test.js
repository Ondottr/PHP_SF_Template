// Example in `./src/Tests/Performance/loadTest.js`
import http from 'k6/http';
import { check, sleep } from 'k6';

export default function () {
  let res = http.get('https://127.0.0.1:7000');
  check(res, { 'status was 200': (r) => r.status == 200 });
  sleep(1);
}
