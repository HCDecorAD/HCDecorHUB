package com.hcdecor.mobileagent

object AgentCore {
    enum class Phase { P1,P2,P3,P4,P5,P6,P7,P8,P9,P10 }
    enum class Mode { ECO, ACTIVE, TURBO }
    data class Capability(
        val readMedia:Boolean=true,
        val writeMedia:Boolean=true,
        val trash:Boolean=true,
        val pcBridge:Boolean=false
    )
    data class Command(
        val jobId:String,
        val target:String,
        val action:String,
        val mutation:Boolean=false,
        val verify:Boolean=true
    )
    fun route(c: Command): String =
        if (c.target == "MOBILE") "DIRECT_MOBILE" else "IMASTER_ROUTER"
    fun deletePolicy(permanent:Boolean): String =
        if (permanent) "REQUIRE_CONFIRM" else "TRASH_VERIFY"
}
