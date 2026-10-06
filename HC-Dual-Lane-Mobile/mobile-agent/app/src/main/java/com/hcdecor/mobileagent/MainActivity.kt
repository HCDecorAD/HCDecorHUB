package com.hcdecor.mobileagent
import android.app.Activity
import android.os.Bundle
import android.widget.TextView

class MainActivity : Activity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val view = TextView(this)
        view.text = "HC Mobile Agent\nP1 Bootstrap READY"
        view.textSize = 22f
        view.setPadding(40, 80, 40, 40)
        setContentView(view)
    }
}
