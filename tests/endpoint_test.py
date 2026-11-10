"""Run against php -S 127.0.0.1:8765 with APINEX_API_KEY unset."""
import json, urllib.request, urllib.error

def request(method, body=None, content_type='application/json', extra=None):
    req = urllib.request.Request('http://127.0.0.1:8765/api/chat.php', data=body, method=method, headers={'Content-Type':content_type, **(extra or {})})
    try: response = urllib.request.urlopen(req)
    except urllib.error.HTTPError as e: response = e
    assert response.headers['Cache-Control'] == 'no-store'
    return response.status, json.load(response)
assert request('GET') == (200, {'ready':False, 'provider_verified':False})
for method, body, status in [('PUT',b'{}',405), ('POST',b'{',400),('POST',b'{"message":[]}',400),('POST',b'{"message":" "}',400),('POST',json.dumps({'message':'a'*1001}).encode(),400),('POST',b'x'*8193,413),('POST',b'{"message":"Apa keahlian Farel?"}',503)]:
    assert request(method, body)[0] == status, (method,status)
assert request('POST',b'{}','text/plain')[0] == 415
assert request('POST',b'{}',extra={'Sec-Fetch-Site':'cross-site'})[0] == 403
print('PASS 10 HTTP cases: readiness, validation, limits, missing key, cross-site requests')

req=urllib.request.Request('http://127.0.0.1:8765/api/guestbook.php',data=json.dumps({'name':'Visitor','message':'Hello','rating':5}).encode(),headers={'Content-Type':'application/json'})
try: response=urllib.request.urlopen(req)
except urllib.error.HTTPError as e: response=e
assert response.status==503, 'Missing server-side limits must fail closed'
print('PASS guestbook fails closed without configuration')
