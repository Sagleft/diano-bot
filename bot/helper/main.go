package main

import (
	"fmt"
	"os"
	"unicode"

	swissknife "github.com/Sagleft/swiss-knife"
	"gopkg.in/robfig/cron.v2"
)

const (
	appName     = "diano-bot"
	devAddress  = ""
	currencyTag = "CRP"
)

func main() {
	swissknife.PrintIntroMessage(appName, devAddress, currencyTag)
	setupCron(os.Getenv("CRON_SPEC"))
}

func parseCronSpec(spec string) string {
	runes := []rune(spec)
	if !unicode.IsDigit(runes[0]) {
		spec = "@" + spec
	}
	return spec
}

func setupCron(cronSpec string) {
	c := cron.New()
	c.AddFunc(parseCronSpec(cronSpec), func() {
		runBot()
	})
	c.Start()
}

func runBot() {
	fmt.Println("run bot..") // TEMP
}
