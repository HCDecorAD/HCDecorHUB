import io,json,unittest
from unittest.mock import patch
from src.adapters.cdp import CDPDiscovery
class Resp(io.BytesIO):
 def __enter__(self):return self
 def __exit__(self,*a):pass
class CDPTests(unittest.TestCase):
 @patch("urllib.request.urlopen")
 def test_filters_and_parses(self,u):
  u.return_value=Resp(json.dumps([{"type":"page","id":"a","url":"https://chatgpt.com/c/abc","title":"A","webSocketDebuggerUrl":"ws://a"},{"type":"page","id":"b","url":"https://example.com","title":"B"}]).encode());p=CDPDiscovery().pages();self.assertEqual(len(p),1);self.assertEqual(p[0]["conversation_id"],"abc")
if __name__=="__main__":unittest.main()
