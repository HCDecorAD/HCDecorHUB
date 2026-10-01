import tempfile,pathlib,unittest
from src.adapters.edge_session_snapshot import snapshot_cids
class SessionSnapshot(unittest.TestCase):
 def test_read_only_extract(self):
  with tempfile.TemporaryDirectory() as d:
   p=pathlib.Path(d)/"Session_1";p.write_bytes(b"SNSS"+b"xhttps://chatgpt.com/c/6abe94ad-1164-83ec-a317-587940e0aea8z")
   self.assertEqual(snapshot_cids(d),["6abe94ad-1164-83ec-a317-587940e0aea8"])
if __name__=="__main__":unittest.main()
