import unittest
from src.core.theme import resolve_theme
class ThemeTests(unittest.TestCase):
 def test_modes(self):
  self.assertEqual(resolve_theme("dark")[0],"dark");self.assertEqual(resolve_theme("light")[0],"light");self.assertEqual(resolve_theme("system",True)[0],"dark")
if __name__=="__main__":unittest.main()
