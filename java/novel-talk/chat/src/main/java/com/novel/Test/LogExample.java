package com.novel.Test;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;
import org.slf4j.event.Level;

public class LogExample {
	
		public  static final Logger logger =  LoggerFactory.getLogger(LogExample.class);
    public static void main(String[] args) {
//        Logger logger = LoggerFactory.getLogger(LogExamples.class);

        System.out.println(logger.isEnabledForLevel(Level.DEBUG));;
        logger.debug("debug");
        logger.info("info");
        logger.error("error");
        logger.trace("trace");
        int age  = 100;
        String name = "홍길동";
        logger.debug("name is {}",name);// 값 입력또한 가능
        logger.debug("age is {}",age);
        logger.debug("hello my name is {} and my age is {}",name,age);
        //17:37:47.758 [main] DEBUG Main.LogExamples - hello my name is 홍길동 and my age is 100
    }
}
