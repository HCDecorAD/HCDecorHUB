@echo off
setlocal
set ROOT=D:\HCDecorHUB\TransportMesh
cd /d "%ROOT%"
start "" /min node mcp-server.mjs
echo IMASTER_MCP_STARTED
