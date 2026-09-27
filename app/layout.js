import "./globals.css";
import ThemeToggle from "../components/ThemeToggle";
export const metadata={title:"HCDecor HUB",description:"HCDecor operations command center"};
const boot=`(()=>{try{const m=localStorage.getItem("hcdecor-theme")||"system";const d=m==="dark"||(m==="system"&&matchMedia("(prefers-color-scheme: dark)").matches);document.documentElement.dataset.theme=d?"dark":"light";document.documentElement.dataset.themeMode=m}catch(e){}})()`;
export default function RootLayout({children}){return <html lang="vi" suppressHydrationWarning><head><script dangerouslySetInnerHTML={{__html:boot}} /></head><body>{children}<ThemeToggle/></body></html>}
